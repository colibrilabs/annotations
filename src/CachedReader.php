<?php

namespace Colibri\Annotations;

use Colibri\Cache\Pool\CacheItemPool;

/**
 * Class CachedReader
 * @package Colibri\Annotations
 */
class CachedReader extends Reader
{
  
  /**
   * @var CacheItemPool
   */
  protected $cache;
  
  /**
   * CachedReader constructor.
   * @param CacheItemPool $cache
   * @param Parser|null $parser
   */
  public function __construct(CacheItemPool $cache, Parser $parser = null)
  {
    parent::__construct($parser);
    
    $this->cache = $cache;
  }
  
  /**
   * @inheritDoc
   */
  public function getClassAnnotations(\ReflectionClass $reflection)
  {
    if (null !== ($cacheItem = $this->cache->getItem($reflection->getName())) && !$cacheItem->isHit()) {
      $cacheItem->set(parent::getClassAnnotations($reflection));
      $this->cache->save($cacheItem);
    }
    
    return $cacheItem->get();
  }
  
  /**
   * @inheritDoc
   */
  public function getMethodAnnotations(\ReflectionMethod $reflection)
  {
    return parent::getMethodAnnotations($reflection);
  }
  
  /**
   * @inheritDoc
   */
  public function getPropertyAnnotations(\ReflectionProperty $reflection)
  {
    return parent::getPropertyAnnotations($reflection);
  }
  
  /**
   * @inheritDoc
   */
  public function getClassAnnotation(\ReflectionClass $reflectionClass, $annotationClass)
  {
    return parent::getClassAnnotation($reflectionClass, $annotationClass);
  }
  
  /**
   * @inheritDoc
   */
  public function getMethodAnnotation(\ReflectionMethod $reflectionMethod, $annotationClass)
  {
    return parent::getMethodAnnotation($reflectionMethod, $annotationClass);
  }
  
  /**
   * @inheritDoc
   */
  public function getPropertyAnnotation(\ReflectionProperty $reflectionProperty, $annotationClass)
  {
    return parent::getPropertyAnnotation($reflectionProperty, $annotationClass);
  }
  
}