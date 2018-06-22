<?php
/**
 * Method return type resolver
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
    public function getReturnType(\ReflectionMethod $method)
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
            $returnTypeName = ($returnType->allowsNull() ? '?' : '') .
                $this->expandLiteralType($returnType->getName(), $methodReflection);
        } else {
            $returnTypeName = $this->expandLiteralType((string) $returnType, $methodReflection);
        }

        return $returnTypeName;
    }

    /**
     * Return literal type name.
     *
     * @param string $literalReturnType
     * @param \ReflectionMethod $methodReflection
     *
     * @return string
     */
    private function expandLiteralType($literalReturnType, \ReflectionMethod $methodReflection)
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
