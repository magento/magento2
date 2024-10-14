<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
