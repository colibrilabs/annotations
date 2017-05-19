<?php

namespace Colibri\Annotations\Annotation;

/**
 * Class Enum
 * @package Colibri\Annotations\Annotation
 * @Annotation()
 * @Target({Target::PROPERTY})
 */
final class Enum
{
  
  /**
   * @var array
   */
  public $values;
  
  /**
   * Enum constructor.
   * @param array $values
   */
  public function __construct(array $values)
  {
    $this->values = $values;
  }
  
}