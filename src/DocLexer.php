<?php

namespace Colibri\Annotations;

use Colibri\Lexer\AbstractLexer;

/**
 * Class DocLexer
 */
class DocLexer extends AbstractLexer
{
  
  const T_UNKNOWN = 1;
  
  const T_NULL = 1000;
  const T_INTEGER = 1001;
  const T_STRING = 1002;
  const T_FLOAT = 1003;
  
  const T_BOOLEAN_TRUE = 2000;
  const T_BOOLEAN_FALSE = 2001;
  
  const T_NULL_TYPE = 3001;
  
  const T_IDENTIFIER = 4000;
  
  const T_AT = 100;
  const T_EQ = 101;
  const T_COMMA = 102;
  const T_NS_SEPARATOR = 103;
  const T_COLON = 104;
  const T_OPEN_BRACE = 105;
  const T_OPEN_CURLY_BRACE = 106;
  const T_CLOSE_BRACE = 107;
  const T_CLOSE_CURLY_BRACE = 108;
  
  /**
   * @var array
   */
  private $map = [
    '@' => self::T_AT,
    '=' => self::T_EQ,
    ',' => self::T_COMMA,
    '\\' => self::T_NS_SEPARATOR,
    ':' => self::T_COLON,
    '(' => self::T_OPEN_BRACE,
    '{' => self::T_OPEN_CURLY_BRACE,
    ')' => self::T_CLOSE_BRACE,
    '}' => self::T_CLOSE_CURLY_BRACE,
    'false' => self::T_BOOLEAN_FALSE,
    'true' => self::T_BOOLEAN_TRUE,
    'null' => self::T_NULL_TYPE,
  ];
  
  /**
   * AnnotationLexer constructor.
   * @param $input
   */
  public function __construct($input)
  {
    $this->setInput($input);
  }
  
  /**
   * @param $token
   * @return mixed
   */
  public function getLiteral($token)
  {
    return str_replace(__NAMESPACE__, 'NS', parent::getLiteral($token));
  }
  
  /**
   * @inheritDoc
   */
  protected function getNonCatchablePatterns()
  {
    return ['\s+', '\*+', '(.)'];
  }
  
  /**
   * @inheritDoc
   */
  protected function getCatchablePatterns()
  {
    return [
      '[a-z0-9_\\\][a-z0-9_\\\]*[a-z_][a-z0-9_]*',
      '(?:[0-9]+(?:[\.][0-9]+)*)',
      '\'(?:[^\'])*\'', '"(?:[^"])*"',
    ];
  }
  
  /**
   * @inheritDoc
   */
  protected function processToken($token)
  {
    $type = static::T_UNKNOWN;
    
    if (isset($this->map[$token])) {
      $type = $this->map[$token];
    } else {
      if ($token[0] === '\'' || $token[0] === '"') {
        $token = trim(trim($token, '"'), "'");
        $type = static::T_STRING;
      } elseif (ctype_alpha($token[0]) || '_' === $token[0] || '\\' === $token[0]) {
        $type = static::T_IDENTIFIER;
      } elseif (is_numeric($token)) {
        $type = (strpos($token, '.') === false) ? static::T_INTEGER : static::T_FLOAT;
      }
    }
    
    return compact('token', 'type');
  }
  
}