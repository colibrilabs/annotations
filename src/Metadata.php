<?php

namespace Subapp\Annotations;

use Subapp\Annotations\Annotation\Annotation;
use Subapp\Annotations\Annotation\Enum;
use Subapp\Annotations\Annotation\Target;

/**
 * Class Metadata
 * @package Subapp\Annotations
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
     * @var ReaderInterface
     */
    protected $reader;
    
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
     */
    public function __construct(\ReflectionClass $reflectionClass)
    {
        $this->reflection = $reflectionClass;
        $this->isAnnotation = (strpos($reflectionClass->getDocComment(), '@Annotation') !== false || $reflectionClass->getName() === Annotation::class);
        $this->hasConstructor = (null !== ($constructor = $reflectionClass->getConstructor()) && $constructor->getNumberOfParameters() > 0);
        
        $this->reader = new Reader();
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
        return $this->reader->getClassAnnotations($this->getReflectionClass());
    }
    
    /**
     * @return Target|null
     */
    public function getTarget()
    {
        if (null === $this->target) {
            foreach ($this->reader->getClassAnnotations($this->getReflectionClass()) as $annotation) {
                if ($annotation instanceof Target) {
                    $this->target = $annotation;
                    break;
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
            
            $this->enumerations = [];
            foreach ($this->getReflectionClass()->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
                if (false !== strpos($property->getDocComment(), '@Enum')) {
                    foreach ($this->reader->getPropertyAnnotations($property) as $annotation) {
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