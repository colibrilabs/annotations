<?php

namespace Colibri\Annotations;

use Colibri\Lexer\LexerException;

/**
 * Class Parser
 * @package Colibri\Annotations
 */
class Parser
{
  
  /**
   * @var DocLexer
   */
  protected $lexer;
  
  /**
   * @var Parser
   */
  protected $innerParser;
  
  /**
   * @var array
   */
  protected $namespaces = [];
  
  /**
   * @var array
   */
  protected $namespaceAliases = [];
  
  /**
   * @var StaticCollection
   */
  protected $annotationMetadata;
  
  /**
   * Parser constructor.
   */
  public function __construct()
  {
    $this->lexer = new DocLexer(null);
    $this->annotationMetadata = StaticCollection::instance('metadata');
  }
  
  /**
   * @param array $namespaces
   */
  public function setNamespaces(array $namespaces)
  {
    $this->namespaces = $namespaces;
  }
  
  /**
   * @param $namespace
   */
  public function addNamespace($namespace)
  {
    $this->namespaces[$namespace] = $namespace;
  }
  
  /**
   * @param $namespace
   * @param $alias
   * @throws AnnotationException
   */
  public function addAliasOf($namespace, $alias)
  {
    $this->namespaceAliases[$alias] = $namespace;
  }
  
  /**
   * @param $input
   * @return array
   */
  public function parse($input)
  {
    $this->lexer->setInput($input);

    return $this->parseAnnotations();
  }
  
  /**
   * @return array
   */
  protected function parseAnnotations()
  {
    $annotations = [];
    
    while ($this->lexer->next()) {
      // Search annotation start
      if ($this->lexer->token['type'] !== DocLexer::T_AT) {
        continue;
      }
      
      $annotations[] = $this->parseAnnotation();
    }
    
    return $annotations;
  }
  
  /**
   * @return mixed
   * @throws AnnotationException
   */
  protected function parseAnnotation()
  {
    $this->toToken(DocLexer::T_IDENTIFIER);

    $identifier = $this->lexer->token['token'];
    $className = $identifier;
    
    if ('\\' !== $identifier[0]) {
      $className = $this->normalizeClassName($className);
    }
    
    if (!$this->classExists($className)) {
      throw new AnnotationException(sprintf('Annotation @%s cannot be loaded', $identifier));
    }
    
    $metadata = $this->getAnnotationMetadata($className);

    $this->toToken(DocLexer::T_OPEN_BRACE);
  
    $values = [];
    if (!$this->lexer->isNext(DocLexer::T_CLOSE_BRACE)) {
      $values = $this->parseValues();
      $values = $this->normalizeValues($values);
    }
    
    $this->toToken(DocLexer::T_CLOSE_BRACE);
    
    if ($metadata->hasConstructor()) {
      $annotation = $metadata->getReflectionClass()->newInstanceArgs($values);
    } else {
      $annotation = $metadata->getReflectionClass()->newInstanceWithoutConstructor();
      foreach ($values as $property => $value) {
        $annotation->{$property} = $value;
      }
    }
    
    return $annotation;
  }
  
  /**
   * @return array
   */
  protected function parseValues()
  {
    $values = [$this->parseValue()];
    
    while ($this->lexer->isNext(DocLexer::T_COMMA)) {

      $this->toToken(DocLexer::T_COMMA);
      $value = $this->parseValue();
  
      if (!is_array($value) && !is_object($value)) {
        $this->syntaxError('either one parameter or associative parameters', $this->lexer->getToken());
      }
      
      $values[] = $value;
      
      if ($this->lexer->isNext(DocLexer::T_CLOSE_BRACE)) {
        break;
      }
    }

    return $values;
  }
  
  /**
   * @return array|bool|float|int|mixed|null|\stdClass
   */
  protected function parseValue()
  {
    $peek = $this->lexer->getNext();
    
    switch ($peek['type']) {
      
      case DocLexer::T_AT:
        $this->toToken(DocLexer::T_AT);
        return $this->parseAnnotation();
      
      case DocLexer::T_OPEN_CURLY_BRACE:
        return $this->parseArray();
      
      case DocLexer::T_IDENTIFIER:
        return $this->parseKeyValue();
      
      case DocLexer::T_STRING:
      case DocLexer::T_INTEGER:
      case DocLexer::T_FLOAT:
      case DocLexer::T_BOOLEAN_FALSE:
      case DocLexer::T_BOOLEAN_TRUE:
      case DocLexer::T_NULL_TYPE:
        return $this->parseScalarValue();
    }
  
    $this->syntaxError('either @Annotation, @Annotation({param:1}) or scalar types');
    
    return null;
  }
  
  /**
   * @return \stdClass
   */
  public function parseKeyValue()
  {
    $this->toToken(DocLexer::T_IDENTIFIER);
    
    $token = $this->lexer->getToken();
    $identifier = $token['token'];

    $value = new \stdClass();
  
    $this->toTokenAny([DocLexer::T_EQ, DocLexer::T_COLON]);

    $value->name = $identifier;
    $value->value = $this->parseValue();

    return $value;
  }
  
  /**
   * @return bool|float|int|null
   */
  public function parseScalarValue()
  {
    $token = $this->lexer->getNext();
    $value = null;
  
    $this->toToken($token['type']);

    switch ($token['type']) {
      case DocLexer::T_STRING:
        $value = $token['token'];
        break;
      case DocLexer::T_INTEGER:
        $value = (int)$token['token'];
        break;
      case DocLexer::T_FLOAT:
        $value = (float)$token['token'];
        break;
      case DocLexer::T_BOOLEAN_FALSE:
        $value = false;
        break;
      case DocLexer::T_BOOLEAN_TRUE:
        $value = true;
        break;
      case DocLexer::T_NULL_TYPE:
        $value = null;
        break;
      default:
        $this->syntaxError('scalar value');
    }

    return $value;
  }
  
  /**
   * @return array
   */
  protected function parseArray()
  {
    $values = [];
    
    $this->toToken(DocLexer::T_OPEN_CURLY_BRACE);
    
    if (!$this->lexer->isNext(DocLexer::T_CLOSE_CURLY_BRACE)) {
      while (!$this->lexer->isNext(DocLexer::T_CLOSE_CURLY_BRACE)) {
        
        if ($this->lexer->isNext(DocLexer::T_COMMA)) {
          $this->lexer->next();
          continue;
        }
  
        $value = $this->parseValue();
        if ($this->lexer->isNextAny([DocLexer::T_COLON, DocLexer::T_EQ])) {
          $this->toTokenAny([DocLexer::T_COLON, DocLexer::T_EQ]);
          $values[$value] = $this->isNext(DocLexer::T_IDENTIFIER)
            ? $this->parseConstant() : $this->parseValue();
        } else {
          $values[] = $value;
        }
      }
    }
    
    $this->toToken(DocLexer::T_CLOSE_CURLY_BRACE);
 
    return $values;
  }
  
  /**
   * @return integer|float|string|array
   * @throws AnnotationException
   */
  protected function parseConstant()
  {
    $this->toToken(DocLexer::T_IDENTIFIER);
  
    $constant = $this->lexer->token['token'];
    
    if (strpos($constant, '::')) {
      list($className, $constantName) = explode('::', $constant);
  
      if ($constantName === 'class') {
        return $className;
      }
      
      if ('\\' !== $className[0]) {
        $className = $this->normalizeClassName($className);
      }
      
      $constant = sprintf('%s::%s', $className, $constantName);
    }
    
    if (!defined($constant)) {
      throw new AnnotationException(sprintf('Constant %s not defined', $constant));
    }
    
    return constant($constant);
  }
  
  /**
   * @param $className
   * @return bool
   */
  protected function classExists($className)
  {
    return class_exists($className);
  }
  
  /**
   * @param $className
   * @return AnnotationMetadata
   */
  protected function getAnnotationMetadata($className)
  {
    if (!$this->annotationMetadata->has($className)) {
      $reflection = new \ReflectionClass($className);
      $this->annotationMetadata->set($className, new AnnotationMetadata($reflection));
    }
    
    return $this->annotationMetadata->get($className);
  }
  
  /**
   * @param $identifier
   * @return string
   */
  protected function normalizeClassName($identifier)
  {
    $className = $identifier;
    
    $position = strrpos($identifier, '\\');
  
    if (($alias = substr($identifier, 0, $position)) && isset($this->namespaceAliases[$alias])) {
      $namespace = $this->namespaceAliases[$alias];
      $className = sprintf('%s\\%s', $namespace, substr($identifier, $position + 1));
    } else {
      foreach ($this->namespaces as $namespace) {
        if ($this->classExists($className = sprintf('%s\\%s', $namespace, $identifier)))
          break;
      }
    }
    
    return $className;
  }
  
  /**
   * @param array $values
   * @return array
   */
  protected function normalizeValues(array $values)
  {
    $normalized = [];
  
    foreach ($values as $keyName => $value) {
      if ($value instanceof \stdClass) {
        $normalized[$value->name] = $value->value;
      } else {
        $normalized[] = $value;
      }
    }
    
    return $normalized;
  }
  
  /**
   * @param $expect
   * @param array $token
   * @throws LexerException
   */
  protected function syntaxError($expect, array $token = [])
  {
    $token = empty($token) ? $this->lexer->getNext() : $token;
    $position = $token['position'];
    
    throw new LexerException(sprintf(
      "Syntax error. Expect %s got '%s' at position %d",
      $this->lexer->getLiteral($expect), $token['token'], $position
    ));
  }
  
  /**
   * @param integer $token
   * @throws LexerException
   */
  protected function toToken($token)
  {
    $this->lexer->toToken($token) || $this->syntaxError($token);
  }
  
  /**
   * @param array|integer[] $tokens
   * @throws LexerException
   */
  protected function toTokenAny(array $tokens)
  {
    $this->lexer->toTokenAny($tokens)
      || $this->syntaxError(sprintf('either %s', implode(' or ', array_map([$this->lexer, 'getLiteral'], $tokens))));
  }
  
  /**
   * @param integer $token
   * @return bool
   */
  protected function isNext($token)
  {
    return $this->lexer->isNext($token);
  }
  
}