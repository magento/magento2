<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Reflection;

/**
 * Resolve native method types for Web API metadata.
 */
class NativeTypeResolver
{
    /**
     * Resolve a native return type, or return null when none is declared.
     *
     * @param \ReflectionMethod $method
     * @return array|null
     * @throws \InvalidArgumentException
     */
    public function resolveReturn(\ReflectionMethod $method): ?array
    {
        $type = $method->getReturnType();
        if ($type === null) {
            return null;
        }
        if (!$type instanceof \ReflectionNamedType) {
            throw $this->unsupportedReturn($method, $type);
        }

        return [
            'type' => $this->resolveReturnName($method, $type),
            'isRequired' => in_array($type->getName(), ['mixed', 'void'], true) || !$type->allowsNull(),
            'description' => '',
            'parameterCount' => $method->getNumberOfRequiredParameters(),
        ];
    }

    /**
     * Reject composite native parameter types.
     *
     * @param \ReflectionParameter $parameter
     * @return void
     * @throws \InvalidArgumentException
     */
    public function assertNamedParameter(\ReflectionParameter $parameter): void
    {
        $type = $parameter->getType();
        if ($type === null || $type instanceof \ReflectionNamedType) {
            return;
        }
        $method = $parameter->getDeclaringFunction();
        throw new \InvalidArgumentException(sprintf(
            'Parameter "$%4$s" of %1$s::%2$s() declares type "%3$s", which the Web API cannot map. '
                . 'Use a single type.',
            $parameter->getDeclaringClass()->getName(),
            $method->getName(),
            (string) $type,
            $parameter->getName()
        ));
    }

    /**
     * Resolve a named return type to the metadata spelling.
     *
     * @param \ReflectionMethod $method
     * @param \ReflectionNamedType $type
     * @return string
     * @throws \InvalidArgumentException
     */
    private function resolveReturnName(\ReflectionMethod $method, \ReflectionNamedType $type): string
    {
        $name = $type->getName();
        if (in_array($name, ['array', 'iterable'], true)) {
            throw new \InvalidArgumentException(sprintf(
                'Method %1$s::%2$s() declares return type "%3$s" without an item type. '
                    . 'Add a @return annotation such as Foo[].',
                $method->getDeclaringClass()->getName(),
                $method->getName(),
                (string) $type
            ));
        }
        if (in_array($name, ['self', 'static', 'parent'], true)) {
            return $this->resolveRelativeName($method, $type);
        }
        if (!$type->isBuiltin()) {
            return '\\' . $name;
        }
        if (in_array($name, ['int', 'string', 'bool', 'float', 'mixed', 'void'], true)) {
            return $name;
        }
        throw $this->unsupportedReturn($method, $type);
    }

    /**
     * Resolve a relative type against the method's declaring class.
     *
     * @param \ReflectionMethod $method
     * @param \ReflectionNamedType $type
     * @return string
     * @throws \InvalidArgumentException
     */
    private function resolveRelativeName(\ReflectionMethod $method, \ReflectionNamedType $type): string
    {
        $class = $method->getDeclaringClass();
        if ($type->getName() === 'parent') {
            $class = $class->getParentClass();
            if ($class === false) {
                throw $this->unsupportedReturn($method, $type);
            }
        }
        return '\\' . $class->getName();
    }

    /**
     * Build the diagnostic for an unsupported return type.
     *
     * @param \ReflectionMethod $method
     * @param \ReflectionType $type
     * @return \InvalidArgumentException
     */
    private function unsupportedReturn(\ReflectionMethod $method, \ReflectionType $type): \InvalidArgumentException
    {
        return new \InvalidArgumentException(sprintf(
            'Method %1$s::%2$s() declares return type "%3$s", which the Web API cannot map. '
                . 'Use a single supported type or add a @return annotation.',
            $method->getDeclaringClass()->getName(),
            $method->getName(),
            (string) $type
        ));
    }
}
