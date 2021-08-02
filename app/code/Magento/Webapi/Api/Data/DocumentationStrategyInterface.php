<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Api\Data\ComplexTypeStrategy;

use ReflectionClass;
use ReflectionProperty;

/**
 * Interface DocumentationStrategyInterface
 */
interface DocumentationStrategyInterface
{
    /**
     * Returns documentation for complex type property.
     *
     * @param ReflectionProperty $property
     *
     * @return string
     */
    public function getPropertyDocumentation(ReflectionProperty $property): string;

    /**
     * Returns documentation for complex type.
     *
     * @param ReflectionClass $class
     *
     * @return string
     */
    public function getComplexTypeDocumentation(ReflectionClass $class): string;
}
