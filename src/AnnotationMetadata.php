<?php

namespace Colibri\Annotations;

/**
 * Class AnnotationMetadata
 * @package Colibri\Annotations
 */
class AnnotationMetadata
{
  
  /**
   * @var \ReflectionClass
   */
  protected $reflection;
  
  /**
   * @var Parser
   */
  protected $parser;
  
  /**
   * @var bool
   */
  protected $isAnnotation = false;
  
  /**
   * @var bool
   */
  protected $hasConstructor = false;
  
  /**
   * AnnotationMetadata constructor.
   * @param \ReflectionClass $reflectionClass
   */
  public function __construct(\ReflectionClass $reflectionClass)
  {
    $this->reflection = $reflectionClass;
    $this->isAnnotation = strpos($reflectionClass->getDocComment(), '@Annotation') !== false;
    $this->hasConstructor = (null !== ($constructor = $reflectionClass->getConstructor()) && $constructor->getNumberOfParameters() > 0);
  }
  
  /**
   * @return bool
   */
  public function hasConstructor()
  {
    return $this->hasConstructor;
  }
  
  /**
   * @return bool
   */
  public function isAnnotation()
  {
    return $this->isAnnotation;
  }
  
  /**
   * @return \ReflectionClass
   */
  public function getReflectionClass()
  {
    return $this->reflection;
  }
  
}