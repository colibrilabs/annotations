<?php

namespace Colibri\Annotations;

use Colibri\Annotations\Annotation\Enum;
use Colibri\Annotations\Annotation\Target;

/**
 * Class Metadata
 * @package Colibri\Annotations
 */
class Metadata
{
  
  /**
   * @var \ReflectionClass
   */
  protected $reflection;
  
  /**
   * @var array
   */
  protected $annotations;
  
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
   * @var Target
   */
  protected $target;
  
  /**
   * @var array|Enum[]
   */
  protected $enumerations;
  
  /**
   * AnnotationMetadata constructor.
   * @param \ReflectionClass $reflectionClass
   * @param Parser $parser
   */
  public function __construct(\ReflectionClass $reflectionClass, Parser $parser)
  {
    $this->reflection = $reflectionClass;
    $this->isAnnotation = strpos($reflectionClass->getDocComment(), '@Annotation') !== false;
    $this->hasConstructor = (null !== ($constructor = $reflectionClass->getConstructor()) && $constructor->getNumberOfParameters() > 0);
    
    $this->parser = $parser;
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
   * @return array
   */
  public function getAnnotations()
  {
    $reflection = $this->getReflectionClass();
    $context = sprintf('class %s {}', $reflection->getName());
    
    return $this->parser->parse($reflection->getDocComment(), $context);
  }
  
  /**
   * @return Target|null
   */
  public function getTarget()
  {
    if (null === $this->target) {
      foreach ($this->getAnnotations() as $annotation) {
        if ($annotation instanceof Target) {
          $this->target = $annotation; break;
        }
      }
    }
    
    return $this->target;
  }
  
  /**
   * @return array|Enum[]
   */
  public function getEnumeration()
  {
    if (null === $this->enumerations) {
      $reflection = $this->getReflectionClass();
  
      $this->enumerations = [];
      foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
        if (false !== strpos($property->getDocComment(), '@Enum')) {
  
          $this->parser->setTarget(Target::PROPERTY);

          $context = sprintf('property %s::$%s', $reflection->getName(), $property->getName());
          foreach ($this->parser->parse($property->getDocComment(), $context) as $annotation) {
            if ($annotation instanceof Enum) {
              $this->enumerations[$property->getName()] = $annotation;
              break;
            }
          }
        }
      }
    }
    
    return $this->enumerations;
  }
  
  /**
   * @return \ReflectionClass
   */
  public function getReflectionClass()
  {
    return $this->reflection;
  }
  
}