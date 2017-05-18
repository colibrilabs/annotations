<?php

namespace Colibri\Annotations;

use Colibri\Loader\Loader;

/**
 * Class AnnotationLoader
 * @package Colibri\Annotations
 */
class AnnotationLoader
{
  
  /**
   * @var Loader
   */
  public static $loader;
  
  /**
   * @return Loader
   */
  public static function getLoader()
  {
    if (null === static::$loader) {
      static::$loader = new Loader();
    }
    
    return static::$loader;
  }
  
  /**
   * @param array $directories
   */
  public static function registerAutoloadDirectories(array $directories)
  {
    static::getLoader()->registerDirectories($directories)->register();
  }
  
  /**
   * @param string $namespace
   * @param string $directory
   */
  public static function registerAutoloadNamespace($namespace, $directory)
  {
    static::getLoader()->registerDirectories([$namespace => $directory,])->register();
  }
  
  /**
   * @param string $annotation
   */
  public static function registerAnnotation($annotation)
  {
    include_once $annotation;
  }
  
}