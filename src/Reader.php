<?php

namespace Subapp\Annotations;

use Subapp\Annotations\Annotation\Target;

/**
 * Class Reader
 * @package Subapp\Annotations
 */
class Reader implements ReaderInterface
{
  
  /**
   * @var Parser
   */
  protected $parser;
  
  /**
   * Reader constructor.
   * @param Parser|null $parser
   */
  public function __construct(Parser $parser = null)
  {
    $this->parser = $parser ?: new Parser();
    $this->parser->setIgnoreNotImportedAnnotation(true);
    $this->parser->addNamespace(sprintf('%s\\Annotation', __NAMESPACE__));
  }
  
  /**
   * @inheritDoc
   */
  public function getClassAnnotations(\ReflectionClass $reflection)
  {
    $this->parser->setTarget(Target::CLAZZ);
    $context = sprintf('class %s {}', $reflection->getName());
    
    return $this->parser->parse($reflection->getDocComment(), $context);
  }
  
  /**
   * @inheritDoc
   */
  public function getMethodAnnotations(\ReflectionMethod $reflection)
  {
    $this->parser->setTarget(Target::METHOD);
    $context = sprintf('%s::%s();', $reflection->getDeclaringClass()->getName(), $reflection->getName());
  
    return $this->parser->parse($reflection->getDocComment(), $context);
  }
  
  /**
   * @inheritDoc
   */
  public function getPropertyAnnotations(\ReflectionProperty $reflection)
  {
    $this->parser->setTarget(Target::PROPERTY);
    $context = sprintf('%s::$%s;', $reflection->getDeclaringClass()->getName(), $reflection->getName());
  
    return $this->parser->parse($reflection->getDocComment(), $context);
  }
  
  /**
   * @inheritDoc
   */
  public function getClassAnnotation(\ReflectionClass $reflectionClass, $annotationClass)
  {
    foreach ($this->getClassAnnotations($reflectionClass) as $annotation) {
      if ($annotation instanceof $annotationClass) {
        return $annotation;
      }
    }
    
    return null;
  }
  
  /**
   * @inheritDoc
   */
  public function getMethodAnnotation(\ReflectionMethod $reflectionMethod, $annotationClass)
  {
    foreach ($this->getMethodAnnotations($reflectionMethod) as $annotation) {
      if ($annotation instanceof $annotationClass) {
        return $annotation;
      }
    }
    
    return null;
  }
  
  /**
   * @inheritDoc
   */
  public function getPropertyAnnotation(\ReflectionProperty $reflectionProperty, $annotationClass)
  {
    foreach ($this->getPropertyAnnotations($reflectionProperty) as $annotation) {
      if ($annotation instanceof $annotationClass) {
        return $annotation;
      }
    }
    
    return null;
  }
  
  /**
   * @inheritDoc
   */
  public function getParser()
  {
    return $this->parser;
  }
  
}