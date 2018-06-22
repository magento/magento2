<?php
/**
 * Method return type resolver
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Code\Generator\Method;

class ReturnTypeResolver
{
    /**
     * Retrieve return type info.
     *
     * @param \ReflectionMethod $method
     *
     * @return null|string
     */
    public function getReturnType(\ReflectionMethod $method) : ?string
    {
        $returnType = method_exists($method, 'getReturnType')
            ? $method->getReturnType()
            : null;

        if ($returnType !== null) {
            $returnType = $this->getReturnTypeName($returnType, $method);
        }

        return $returnType;
    }

    /**
     * Retrieve return type name.
     *
     * @param \ReflectionType $returnType
     * @param \ReflectionMethod $methodReflection
     * @return string
     */
    private function getReturnTypeName(\ReflectionType $returnType, \ReflectionMethod $methodReflection) : string
    {
        if (method_exists($returnType, 'getName')) {
            return ($returnType->allowsNull() ? '?' : '') .
                $this->expandLiteralType($returnType->getName(), $methodReflection);
        }

        return $this->expandLiteralType((string) $returnType, $methodReflection);
    }

    /**
     * Return literal type name.
     *
     * @param string $literalReturnType
     * @param \ReflectionMethod $methodReflection
     *
     * @return string
     */
    private function expandLiteralType(string $literalReturnType, \ReflectionMethod $methodReflection) : string
    {
        $returnType = $literalReturnType;

        if (strtolower($literalReturnType) == 'self') {
            $returnType = $methodReflection->getDeclaringClass()->getName();
        }

        if (strtolower($literalReturnType) == 'parent') {
            $returnType = $methodReflection->getDeclaringClass()->getParentClass()->getName();
        }

        return $returnType;
    }
}
