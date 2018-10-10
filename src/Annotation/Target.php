<?php

namespace Subapp\Annotations\Annotation;

/**
 * Class Target
 * @package Subapp\Annotations\Annotation
 * @Annotation()
 */
final class Target
{
  
  const ANNOTATION = 1;
  const CLAZZ = 2;
  const METHOD = 4;
  const PROPERTY = 8;
  const ALL = 15;
  
  /**
   * @var array
   */
  static private $toValueMap = [
    'ANNOTATION' => self::ANNOTATION,
    'CLASS' => self::CLAZZ,
    'METHOD' => self::METHOD,
    'PROPERTY' => self::PROPERTY,
    'ALL' => self::ALL,
  ];
  
  /**
   * @var array
   */
  static private $toLiteralMap = [
    self::ANNOTATION => 'ANNOTATION',
    self::CLAZZ => 'CLASS',
    self::METHOD => 'METHOD',
    self::PROPERTY => 'PROPERTY',
    self::ALL => 'ALL',
  ];
  
  /**
   * @var integer
   */
  public $bitmask;
  
  /**
   * @var string
   */
  public $literal;
  
  /**
   * Target constructor.
   * @param array $values
   */
  public function __construct(array $values)
  {
    $bitmask = 0;
    $literal = [];
    
    foreach ($values as $bitvalue) {
      
      if (is_string($bitvalue) && isset(static::$toValueMap[$bitvalue])) {
        $literal[] = $bitvalue;
        $bitvalue = static::$toValueMap[$bitvalue];
      } else if (isset(static::$toLiteralMap[$bitvalue])) {
        $literal[] = static::$toLiteralMap[$bitvalue];
      }
      
      $bitmask |= $bitvalue;
    }
    
    $this->bitmask = $bitmask;
    $this->literal = implode(', ', $literal);
  }
  
}