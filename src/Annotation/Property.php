<?php

namespace Subapp\Annotations\Annotation;

/**
 * Class Property
 * @package Subapp\Annotations\Annotation
 * @Annotation()
 * @Target({Target::CLAZZ, Target::PROPERTY})
 */
final class Property
{
    
    /**
     * @var string
     */
    public $name;
    
    /**
     * @var bool
     * @Enum({true, false})
     */
    public $required = false;
    
}