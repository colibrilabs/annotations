<?php

namespace Colibri\Annotations\Annotation;

/**
 * Class Property
 * @package Colibri\Annotations\Annotation
 * @Annotation
 */
class Property
{
  
  /**
   * @var string
   */
  public $name;
  
  /**
   * @var bool
   */
  public $required = false;
  
}