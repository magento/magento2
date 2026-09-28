<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Utility;

use ReflectionClass;
use ReflectionException;

/**
 * Factory for \ReflectionClass
 */
class ReflectionClassFactory
{
    /**
     * Create a reflection class object
     *
     * @param object|string $objectOrClass
     *
     * @return ReflectionClass
     * @throws ReflectionException
     */
    public function create($objectOrClass): ReflectionClass
    {
        return new ReflectionClass($objectOrClass);
    }
}
