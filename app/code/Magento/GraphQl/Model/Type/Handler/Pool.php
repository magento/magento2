<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type\Handler;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Type\Definition\TypeInterface;
use Magento\GraphQl\Model\Type\Handler\Pool\Complex;
use Magento\GraphQl\Model\Type\HandlerFactory;
use Magento\Framework\GraphQl\TypeFactory;
use Magento\GraphQl\Model\Type\HandlerConfig;

/**
 * Retrieve type's registered in pool, or generate types yet to be instantiated and register them
 */
class Pool
{
    const TYPE_STRING = 'String';
    const TYPE_INT = 'Int';
    const TYPE_BOOLEAN = 'Boolean';
    const TYPE_FLOAT = 'Float';
    const TYPE_ID = 'ID';

    /**
     * @var HandlerFactory
     */
    private $typeHandlerFactory;

    /**
     * @var \Magento\Framework\GraphQl\TypeFactory
     */
    private $typeFactory;

    /**
     * @var HandlerConfig
     */
    private $typeConfig;

    /**
     * @var Complex
     */
    private $complexType;

    /**
     * @var TypeInterface[]
     */
    private $typeRegistry = [];

    /**
     * @param HandlerFactory $typeHandlerFactory
     * @param \Magento\Framework\GraphQl\TypeFactory $typeFactory
     * @param HandlerConfig $typeConfig
     * @param Complex $complexType
     */
    public function __construct(
        HandlerFactory $typeHandlerFactory,
        TypeFactory $typeFactory,
        HandlerConfig $typeConfig,
        Complex $complexType
    ) {
        $this->typeHandlerFactory = $typeHandlerFactory;
        $this->typeFactory = $typeFactory;
        $this->typeConfig = $typeConfig;
        $this->complexType = $complexType;
    }

    /**
     * Get a type of @see TypeInterface or scalar native GraphQL
     *
     * @param string $typeName
     * @return TypeInterface|\GraphQL\Type\Definition\Type
     * @throws \LogicException
     * @throws GraphQlInputException
     */
    public function getType(string $typeName)
    {
        if ($this->isTypeRegistered($typeName)) {
            return $this->typeRegistry[$typeName];
        }

        if ($this->isScalar($typeName)) {
            $this->typeRegistry[$typeName] = $this->typeFactory->createScalar($typeName);
        } else {
            $this->typeRegistry[$typeName] = $this->complexType->getComplexType($typeName);
        }

        return $this->typeRegistry[$typeName];
    }

    /**
     * Register type to Pool's type registry.
     *
     * @param TypeInterface|\GraphQL\Type\Definition\Type $type
     * @throws \LogicException
     */
    public function registerType(TypeInterface $type)
    {
        if (isset($this->typeRegistry[$type->name])) {
            throw new \LogicException('Type name already exists in registry');
        }
        $this->typeRegistry[$type->name] = $type;
    }

    /**
     * Check Pool's type registry and returns true if type has been previously generated
     *
     * @param string $typeName
     * @return bool
     */
    public function isTypeRegistered(string $typeName)
    {
        return isset($this->typeRegistry[$typeName]);
    }

    /**
     * If type is a scalar type
     *
     * @param string $typeName
     * @return bool
     */
    private function isScalar(string $typeName)
    {
        $type = new \ReflectionClass(self::class);
        $constants =  $type->getConstants();
        if (in_array($typeName, $constants)) {
            return true;
        } else {
            return false;
        }
    }
}
