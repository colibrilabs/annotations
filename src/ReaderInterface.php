<?php

namespace Subapp\Annotations;

/**
 * Interface ReaderInterface
 * @package Subapp\Annotations
 */
interface ReaderInterface
{
  
  /**
   * @param \ReflectionClass $reflectionClass
   * @return array
   */
  public function getClassAnnotations(\ReflectionClass $reflectionClass);
  
  /**
   * @param \ReflectionMethod $reflectionMethod
   * @return array
   */
  public function getMethodAnnotations(\ReflectionMethod $reflectionMethod);
  
  /**
   * @param \ReflectionProperty $reflectionProperty
   * @return array
   */
  public function getPropertyAnnotations(\ReflectionProperty $reflectionProperty);
  
  /**
   * @param \ReflectionClass $reflectionClass
   * @param $annotationClass
   * @return array
   */
  public function getClassAnnotation(\ReflectionClass $reflectionClass, $annotationClass);
  
  /**
   * @param \ReflectionMethod $reflectionMethod
   * @param $annotationClass
   * @return array
   */
  public function getMethodAnnotation(\ReflectionMethod $reflectionMethod, $annotationClass);
  
  /**
   * @param \ReflectionProperty $reflectionProperty
   * @param $annotationClass
   * @return array
   */
  public function getPropertyAnnotation(\ReflectionProperty $reflectionProperty, $annotationClass);
  
  /**
   * @return Parser
   */
  public function getParser();
  
}