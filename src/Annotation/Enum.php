<?php

namespace Subapp\Annotations\Annotation;

/**
 * Class Enum
 * @package Subapp\Annotations\Annotation
 * @Annotation()
 * @Target({Target::PROPERTY})
 */
final class Enum
{
    
    /**
     * @var array
     */
    public $values;
    
    /**
     * Enum constructor.
     * @param array $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }
    
}