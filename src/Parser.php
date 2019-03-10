<?php

namespace Subapp\Annotations;

use Subapp\Annotations\Annotation\Stub;
use Subapp\Annotations\Annotation\Target;
use Subapp\Lexer\LexerException;

/**
 * Class Parser
 * @package Subapp\Annotations
 */
class Parser
{
    
    /**
     * @var DocLexer
     */
    protected $lexer;
    
    /**
     * @var string
     */
    protected $context;
    
    /**
     * @var integer
     */
    protected $target;
    
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
     * @var bool
     */
    protected $ignoreNotImportedAnnotation = false;
    
    /**
     * @var bool
     */
    protected $isInner = false;
    
    /**
     * Parser constructor.
     */
    public function __construct()
    {
        $this->lexer = new DocLexer(null);
        $this->annotationMetadata = StaticCollection::instance('metadata');
        $this->addNamespace(sprintf('%s\\Annotation', __NAMESPACE__));
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
     * @return bool
     */
    public function isIgnoreNotImportedAnnotation()
    {
        return (boolean)$this->ignoreNotImportedAnnotation;
    }
    
    /**
     * @param bool $ignoreNotImportedAnnotation
     */
    public function setIgnoreNotImportedAnnotation($ignoreNotImportedAnnotation)
    {
        $this->ignoreNotImportedAnnotation = (boolean)$ignoreNotImportedAnnotation;
    }
    
    /**
     * @return bool
     */
    public function isInner()
    {
        return $this->isInner;
    }
    
    /**
     * @param bool $isInner
     */
    public function setIsInner($isInner)
    {
        $this->isInner = $isInner;
    }
    
    /**
     * @return int
     */
    public function getTarget()
    {
        return (int)$this->target;
    }
    
    /**
     * @param int $target
     */
    public function setTarget($target)
    {
        $this->target = (int)$target;
    }
    
    /**
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }
    
    /**
     * @param        $input
     * @param string $context
     * @return array
     */
    public function parse($input, $context = null)
    {
        $this->lexer->tokenize(trim($input, '/'));
        $this->context = $context;
        
        return $this->parseAnnotations();
    }
    
    /**
     * @return array
     */
    protected function parseAnnotations()
    {
        $annotations = [];
        $this->lexer->rewind();
        
        do {
            // Skip to next @ if it exist
            if ($this->lexer->token->getType() !== DocLexer::T_AT) {
                continue;
            }
            
            // this is mean is annotation start and it's outer.
            // next annotation  occurrence will be inner
            $this->setIsInner(false);
            
            // After @ start parse annotation
            if (null !== ($annotation = $this->parseAnnotation())) {
                $annotations[] = $annotation;
            }
        } while ($this->lexer->next());
        
        return $annotations;
    }
    
    /**
     * @return mixed
     * @throws AnnotationException
     */
    protected function parseAnnotation()
    {
        $this->toToken(DocLexer::T_IDENTIFIER);
        
        $identifier = $this->lexer->token->getToken();
        $className = $identifier;
        
        if ('\\' !== $identifier[0]) {
            $className = $this->normalizeClassName($className);
        }
        
        if (!$this->classExists($className)) {
            if ($this->isIgnoreNotImportedAnnotation() === false) {
                throw new AnnotationException(sprintf('Annotation @%s cannot be loaded in context %s',
                    $identifier, $this->getContext()));
            } elseif ((ctype_upper($identifier[0]) || '\\' === $identifier[0])) {
                $className = Stub::class;
            } else {
                return null;
            }
        }
        
        $metadata = $this->getAnnotationMetadata($className);
        
        $bitmask = $this->isInner() ? (Target::ANNOTATION | $this->getTarget()) : $this->getTarget();
        $this->setIsInner(true);
        
        // Check target permissions if target allowed
        if (0 !== $bitmask && null !== ($target = $metadata->getTarget())) {
            if (!($target->bitmask & $bitmask)) {
                throw new AnnotationException(sprintf('Annotation @%s is not allowed to used on %s. You can use only on %s',
                    $className, $this->getContext(), $target->literal));
            }
        }
        
        $values = [];
        
        $this->toToken(DocLexer::T_OPEN_BRACE);
        if (!$this->lexer->isNext(DocLexer::T_CLOSE_BRACE)) {
            $values = $this->normalizeValues($this->parseValues());
        }
        $this->toToken(DocLexer::T_CLOSE_BRACE);
        
        // Inject non founded identifier to Stub annotation
        if ($className === Stub::class) {
            $values['instead'] = $identifier;
        }
        
        // Validate ENUM values on properties
        foreach ($metadata->getEnumeration() as $property => $enum) {
            if (isset($values[$property]) && !in_array($values[$property], $enum->values, true)) {
                $valueDumped = is_object($values[$property]) ? get_class($values[$property]) : var_export($values[$property], true);
                throw new AnnotationException(sprintf("Enumeration error. Value %s is not allowed to used on %s::\$%s in context %s",
                    $valueDumped, $className, $property, $this->getContext()));
            }
        }
        
        if ($metadata->hasConstructor()) {
            $annotation = $metadata->getReflectionClass()->newInstanceArgs($values);
        } else {
            
            $annotation = $metadata->getReflectionClass()->newInstanceWithoutConstructor();
            $propertyValues = [];
            
            foreach ($values as $property => $value) {
                if (is_numeric($property)) {
                    $propertyValues[$property] = $value;
                } else {
                    $annotation->{$property} = $value;
                }
            }
            
            $annotation->{'values'} = $propertyValues;
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
        $next = $this->lexer->getNext();
        
        switch ($next->getType()) {
            
            case DocLexer::T_AT:
                $this->toToken(DocLexer::T_AT);
                return $this->parseAnnotation();
            
            case DocLexer::T_OPEN_CURLY_BRACE:
                return $this->parseArray();
            
            case DocLexer::T_IDENTIFIER:
                return $this->parseArgument();
            
            case DocLexer::T_STRING:
            case DocLexer::T_INTEGER:
            case DocLexer::T_FLOAT:
            case DocLexer::T_BOOLEAN_FALSE:
            case DocLexer::T_BOOLEAN_TRUE:
            case DocLexer::T_NULL_TYPE:
                return $this->parseScalarValue();
        }
        
        $this->syntaxError('either @Annotation, Identifier or scalar types');
        
        return null;
    }
    
    /**
     * @return array|float|int|\stdClass|string
     */
    public function parseArgument()
    {
        $currentToken = $this->lexer->getToken();
        $this->toToken(DocLexer::T_IDENTIFIER);
        
        $isComparatorNext = $this->lexer->isNextAny([DocLexer::T_EQ, DocLexer::T_COLON]);
        $this->lexer->backToToken($currentToken->getType());
        
        return $isComparatorNext ? $this->parseKeyValue() : $this->parseClassIdentifier();
    }
    
    /**
     * @return \stdClass
     */
    public function parseKeyValue()
    {
        $this->toToken(DocLexer::T_IDENTIFIER);
        
        $token = $this->lexer->getToken();
        $identifier = $token->getToken();
        
        $value = new \stdClass();
        
        $this->toTokenAny([DocLexer::T_EQ, DocLexer::T_COLON]);
        
        $value->name = $identifier;
        $value->value = $this->isNext(DocLexer::T_IDENTIFIER)
            ? $this->parseClassIdentifier() : $this->parseValue();
        
        return $value;
    }
    
    /**
     * @return bool|float|int|null
     */
    public function parseScalarValue()
    {
        $token = $this->lexer->getNext();
        $value = $token->getToken();
        
        $this->toToken($value);
        
        switch ($token->getType()) {
            case DocLexer::T_INTEGER:
                $value = (int)$value;
                break;
            case DocLexer::T_FLOAT:
                $value = (float)$value;
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
                    $values[$value] = $this->parseValue();
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
    protected function parseClassIdentifier()
    {
        $this->toToken(DocLexer::T_IDENTIFIER);
        
        $classIdentifier = $this->lexer->getToken()->getToken();
        
        if (strpos($classIdentifier, '::')) {
            list($className, $identifierName) = explode('::', $classIdentifier);
            
            if ('\\' !== $className[0]) {
                $className = $this->normalizeClassName($className);
            }
            
            // Method calling
            if ($this->lexer->isNext(DocLexer::T_OPEN_BRACE)) {
                throw new AnnotationException('Method calling not supported yet. Please use class constants only for ClassIdentifier');
            }
            
            // Class properties
            if ('$' === $identifierName[0]) {
                throw new AnnotationException('Class properties access not supported yet. Please use class constants only for ClassIdentifier');
            }
            
            if (!$this->classExists($className)) {
                throw new AnnotationException(sprintf("Could not found class '%s' with constant '%s'", $className, $identifierName));
            }
            
            if ($identifierName === 'class') {
                return $className;
            }
            
            $classIdentifier = sprintf('%s::%s', $className, $identifierName);
        }
        
        if (!defined($classIdentifier)) {
            throw new AnnotationException(sprintf("Constant '%s' not defined", $classIdentifier));
        }
        
        return constant($classIdentifier);
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
     * @return Metadata
     */
    protected function getAnnotationMetadata($className)
    {
        if (!$this->annotationMetadata->has($className)) {
            $reflection = new \ReflectionClass($className);
            $this->annotationMetadata->set($className, new Metadata($reflection));
        }
        
        return $this->annotationMetadata->get($className);
    }
    
    /**
     * @return Parser
     */
    public function getNewParser()
    {
        $parser = new Parser();
        $parser->setIgnoreNotImportedAnnotation(true);
        $parser->addNamespace(sprintf('%s\\Annotation', __NAMESPACE__));
        
        return $parser;
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
     * @param       $expect
     * @param array $token
     * @throws LexerException
     */
    protected function syntaxError($expect, array $token = [])
    {
        $token = empty($token) ? $this->lexer->getNext() : $token;
        $position = $token['position'];
        
        throw new LexerException(sprintf(
            "Syntax error. Expect %s got '%s' at position %d in context %s",
            $this->lexer->getLiteral($expect), $token['token'], $position, $this->getContext()
        ));
    }
    
    /**
     * @param integer $token
     */
    protected function toToken($token)
    {
        $this->lexer->toToken($token) || $this->syntaxError($token);
    }
    
    /**
     * @param array|integer[] $tokens
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