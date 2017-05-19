<?php

namespace Colibri\Annotations\Annotation;

/**
 * Class Property
 * @package Colibri\Annotations\Annotation
 * @Annotation()
 * @Target({Target::ANNOTATION})
 */
final class Property
{
  
  /**
   * @var string
   */
  public $name;
  
  /**
   * @var bool
   * @Enum({true, false})
   */
  public $required = false;
  
}