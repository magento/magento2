<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Config\Data;

use Magento\Framework\GraphQl\Config\Element\FieldInterface;
use Magento\Framework\GraphQl\Schema\Type\ScalarTypes;
use Magento\Framework\GraphQl\Schema\TypeInterface;
use Magento\Framework\GraphQl\Schema\TypeFactory;

/**
 * Processor for wrapped types for both custom and scalar types
 */
class WrappedTypeProcessor
{
    /**
     * @var ScalarTypes
     */
    private $scalarTypes;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @param TypeFactory $typeFactory
     * @param ScalarTypes $scalarTypes
     */
    public function __construct(TypeFactory $typeFactory, ScalarTypes $scalarTypes)
    {
        $this->typeFactory = $typeFactory;
        $this->scalarTypes = $scalarTypes;
    }

    /**
     * Determine the wrapped type from field
     *
     * Examples: nullable or required
     *
     * @param FieldInterface $field
     * @param TypeInterface $object
     * @return TypeInterface
     */
    public function processWrappedType(FieldInterface $field, ?TypeInterface $object = null) : TypeInterface
    {
        return $this->processIsNullable($field, $this->processIsList($field, $object));
    }

    /**
     * Determine the wrapped type from field.
     *
     * Examples: nullable or required.
     *
     * @param FieldInterface $field
     * @param TypeInterface $object
     * @return \GraphQL\Type\Definition\Type
     */
    public function processScalarWrappedType(
        FieldInterface $field,
        ?TypeInterface $object = null
    ) : \GraphQL\Type\Definition\Type {
        if (!$object) {
            $object = $this->scalarTypes->getScalarTypeInstance($field->getTypeName());
        }
        return $this->processScalarIsNullable($field, $this->processScalarIsList($field, $object));
    }

    /**
     * Return passed in type wrapped as a non null type if definition determines necessary.
     *
     * @param FieldInterface $field
     * @param TypeInterface $object
     * @return TypeInterface
     */
    private function processIsNullable(FieldInterface $field, ?TypeInterface $object = null) : TypeInterface
    {
        if ($field->isRequired()) {
            return $this->typeFactory->createNonNull($object);
        }
        return $object;
    }

    /**
     * Return passed in type wrapped as a list if definition determines necessary.
     *
     * @param FieldInterface $field
     * @param TypeInterface $object
     * @return TypeInterface
     */
    private function processIsList(FieldInterface $field, ?TypeInterface $object = null) : TypeInterface
    {
        if ($field->isList()) {
            if ($field instanceof \Magento\Framework\GraphQl\Config\Element\Argument) {
                if ($field->areItemsRequired()) {
                    $object = $this->typeFactory->createNonNull($object);
                }
            }
            return $this->typeFactory->createList($object);
        }
        return $object;
    }

    /**
     * Return passed in scalar type wrapped as a non null type if definition determines necessary.
     *
     * @param FieldInterface $field
     * @param \GraphQL\Type\Definition\Type $object
     * @return \GraphQL\Type\Definition\Type
     */
    private function processScalarIsNullable(
        FieldInterface $field,
        ?\GraphQL\Type\Definition\Type $object = null
    ) : \GraphQL\Type\Definition\Type {
        $object = $object ?: $this->scalarTypes->getScalarTypeInstance($field->getTypeName());
        if ($field->isRequired()) {
            return $this->scalarTypes->createNonNull($object);
        }
        return $object;
    }

    /**
     * Return passed in scalar type wrapped as a list if definition determines necessary.
     *
     * @param FieldInterface $field
     * @param \GraphQL\Type\Definition\Type $object
     * @return \GraphQL\Type\Definition\Type
     */
    private function processScalarIsList(
        FieldInterface $field,
        ?\GraphQL\Type\Definition\Type $object = null
    ) : \GraphQL\Type\Definition\Type {
        $object = $object ?: $this->scalarTypes->getScalarTypeInstance($field->getTypeName());
        if ($field->isList()) {
            if ($field instanceof \Magento\Framework\GraphQl\Config\Element\Argument) {
                if ($field->areItemsRequired()) {
                    $object = $this->scalarTypes->createNonNull($object);
                }
            }
            return $this->scalarTypes->createList($object);
        }
        return $object;
    }
}
