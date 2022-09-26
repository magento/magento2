<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework;

/**
 * Returns the return type of reflection method
 */
trait GetReflectionMethodReturnTypeValueTrait
{
    /**
     * Returns return type
     *
     * @param \ReflectionMethod $method
     * @return string|null
     */
    private function getReturnTypeValue(\ReflectionMethod $method): ?string
    {
        $returnTypeValue = null;
        $returnType = $method->getReturnType();
        if ($returnType) {
            if ($returnType instanceof \ReflectionUnionType || $returnType instanceof \ReflectionIntersectionType) {
                $returnTypeValue = [];
                foreach ($method->getReturnType()->getTypes() as $type) {
                    $returnTypeValue[] =  $type->getName();
                }

                return implode(
                    $returnType instanceof \ReflectionUnionType ? '|' : '&',
                    $returnTypeValue
                );
            }

            $className = $method->getDeclaringClass()->getName();
            $returnTypeValue = ($returnType->allowsNull() && $returnType->getName() !== 'mixed' ? '?' : '');
            $returnTypeValue .= ($returnType->getName() === 'self')
                ? $className ? '\\' . ltrim($className, '\\') : ''
                : $returnType->getName();
        }

        return $returnTypeValue;
    }
}
