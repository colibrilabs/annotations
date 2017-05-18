<?php

namespace Colibri\Annotations;

use Colibri\Collection\ArrayCollection;

/**
 * Class StaticCollection
 * @package Colibri\Annotations
 */
class StaticCollection extends ArrayCollection
{
  
  /**
   * @var static[]
   */
  protected static $instances;
  
  /**
   * @param string $name
   * @return static
   */
  public static function instance($name)
  {
    if (!static::$instances[$name] && !(static::$instances[$name] instanceof static)) {
      static::$instances[$name] = new static();
    }
    
    return static::$instances[$name];
  }
  
}