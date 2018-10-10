<?php

namespace Subapp\Annotations;

use Subapp\Collection\ArrayCollection;

/**
 * Class StaticCollection
 * @package Subapp\Annotations
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