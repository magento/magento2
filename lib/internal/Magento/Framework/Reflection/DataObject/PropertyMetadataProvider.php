<?php
declare(strict_types=1);

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Reflection\DataObject;

/**
 * Provider for reflection/type metadata for data object properties.
 *
 * This class intentionally contains only pure metadata concerns and a
 * cache of public properties. Orchestration (skipping, value processing,
 * etc.) remains in the DataObjectProcessor.
 *
 */
class PropertyMetadataProvider
{
    /**
     * Cache of public properties per data object class name.
     *
     * @var array<string, \ReflectionProperty[]>
     */
    private array $publicPropertiesCache = [];

    /**
     * Return public properties for a given data object type (cached).
     *
     * @param string $dataObjectType
     * @return \ReflectionProperty[]
     */
    public function getPublicProperties(string $dataObjectType): array
    {
        if (!isset($this->publicPropertiesCache[$dataObjectType])) {
            $reflectionClass = new \ReflectionClass($dataObjectType);
            $this->publicPropertiesCache[$dataObjectType] = $reflectionClass->getProperties(
                \ReflectionProperty::IS_PUBLIC
            );
        }

        return $this->publicPropertiesCache[$dataObjectType];
    }

    /**
     * Resolve property type metadata for serialization.
     *
     * @param \ReflectionProperty $property
     * @return array{type: string|null, isRequired: bool}
     */
    public function getPropertyMetadata(\ReflectionProperty $property): array
    {
        $type = $property->getType();
        if ($type === null) {
            return ['type' => null, 'isRequired' => false];
        }

        $allowsNull = $type->allowsNull();
        $typeName = null;

        $typeName = match (true) {
            $type instanceof \ReflectionUnionType => $this->getUnionTypeName($type),
            $type instanceof \ReflectionIntersectionType => null, // intersection types fall back to runtime class.
            $type instanceof \ReflectionNamedType => $type->getName(),
            default => null, // default to null for runtime class.
        };

        $typeName = $this->normalizePropertyType($property, $typeName);

        return [
            'type' => $typeName,
            'isRequired' => !$allowsNull,
        ];
    }

    /**
     * Extract a single type name from a union type, preferring class/interface types over built-in types.
     *
     * @param \ReflectionUnionType $type
     * @return string|null
     */
    private function getUnionTypeName(\ReflectionUnionType $type): ?string
    {
        $typeNames = [];
        foreach ($type->getTypes() as $unionType) {
            if ($unionType instanceof \ReflectionNamedType
                && $unionType->getName() !== 'null'
            ) {
                $typeNames[] = $unionType->getName();
            }
        }

        if (empty($typeNames)) {
            return null;
        }

        if (count($typeNames) === 1) {
            return $typeNames[0];
        }

        return $this->resolvePreferredType($typeNames);
    }

    /**
     * Resolves the preferred type from a list of type names.
     *
     * @param string[] $typeNames
     */
    private function resolvePreferredType(array $typeNames): string
    {
        // Prefer a class/interface type when multiple union types are present
        foreach ($typeNames as $name) {
            if ($this->isObjectType($name)) {
                return $name;
            }
        }

        // Prefer non-builtin types if any (e.g., avoid scalar/array/mixed)
        $builtin = [
            'int',
            'float',
            'string',
            'bool',
            'array',
            'iterable',
            'mixed',
            'callable',
            'object'
        ];

        foreach ($typeNames as $name) {
            if (!in_array($name, $builtin, true)) {
                return $name;
            }
        }

        // Fallback to the first available non-null type
        return $typeNames[0];
    }

    /**
     * Normalize property type names for casting.
     *
     * @param \ReflectionProperty $property
     * @param string|null $typeName
     * @return string|null
     */
    private function normalizePropertyType(\ReflectionProperty $property, ?string $typeName): ?string
    {
        if ($typeName === null) {
            return null;
        }

        if ($typeName === 'self' || $typeName === 'static') {
            return $property->getDeclaringClass()->getName();
        }

        if ($typeName === 'parent') {
            $parent = $property->getDeclaringClass()->getParentClass();
            return $parent ? $parent->getName() : null;
        }

        if ($typeName === 'array' || $typeName === 'iterable' || $typeName === 'mixed') {
            return \Magento\Framework\Reflection\TypeProcessor::UNSTRUCTURED_ARRAY;
        }

        return $typeName;
    }

    /**
     * Ensure object values use a compatible return type.
     *
     * @param string|null $returnType
     * @param mixed $value
     * @param \ReflectionProperty $property
     * @return string|null
     */
    public function resolvePropertyReturnType(?string $returnType, $value, \ReflectionProperty $property): ?string
    {
        if (is_array($value) && $returnType === null) {
            return \Magento\Framework\Reflection\TypeProcessor::UNSTRUCTURED_ARRAY;
        }

        if (!is_object($value) || $value instanceof \Magento\Framework\Phrase) {
            return $returnType;
        }

        return $this->resolveObjectReturnType($returnType, $value, $property);
    }

    /**
     * Resolve return types for object property values.
     *
     * @param string|null $returnType
     * @param object $value
     * @param \ReflectionProperty $property
     * @return string|null
     */
    private function resolveObjectReturnType(?string $returnType, object $value, \ReflectionProperty $property): ?string
    {
        if ($returnType === null || !$this->isObjectType($returnType)) {
            return get_class($value);
        }

        if ($returnType === 'self' || $returnType === 'static') {
            return $property->getDeclaringClass()->getName();
        }

        if ($returnType === 'parent') {
            $parent = $property->getDeclaringClass()->getParentClass();
            return $parent ? $parent->getName() : get_class($value);
        }

        return $returnType;
    }

    /**
     * Check whether the type maps to a class or interface.
     *
     * @param string $type
     * @return bool
     */
    public function isObjectType(string $type): bool
    {
        return interface_exists($type, false) || class_exists($type, false);
    }
}
