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
   * AnnotationMetadata constructor.
   * @param \ReflectionClass $reflectionClass
   * @param Parser $parser
   */
  public function __construct(\ReflectionClass $reflectionClass, Parser $parser)
  {
    $this->reflection = $reflectionClass;
    $this->parser = $parser;
  }
  
  public function hasConstructor()
  {
    
  }
  
}