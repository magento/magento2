<?php
/**
 * @see       https://github.com/laminas/laminas-soap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-soap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-soap/blob/master/LICENSE.md New BSD License
 */

namespace Magento\Webapi\Api\Data;

use ReflectionClass;
use ReflectionProperty;

/**
 * Implement this interface to provide contents for <xsd:documentation> elements on complex types
 */
interface DocumentationStrategyInterface
{
    /**
     * Returns documentation for complex type property
     *
     * @param ReflectionProperty $property
     *
     * @return string
     */
    public function getPropertyDocumentation(ReflectionProperty $property);

    /**
     * Returns documentation for complex type
     *
     * @param ReflectionClass $class
     * @return string
     */
    public function getComplexTypeDocumentation(ReflectionClass $class);
}
