<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type\Handler;

use Magento\Framework\GraphQl\Type\Definition\TypeInterface;
use Magento\GraphQl\Model\Type\HandlerFactory;
use Magento\Framework\GraphQl\Type\TypeFactory;

/**
 * Retrieve type's registered in pool, or generate types yet to be instantiated and register them
 */
class Pool
{
    const STRING = 'String';
    const INT = 'Int';
    const BOOLEAN = 'Boolean';
    const FLOAT = 'Float';
    const ID = 'ID';

    /**
     * @var HandlerFactory
     */
    private $typeHandlerFactory;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var TypeInterface[]
     */
    private $typeRegistry = [];

    /**
     * @param HandlerFactory $typeHandlerFactory
     * @param TypeFactory $typeFactory
     */
    public function __construct(
        HandlerFactory $typeHandlerFactory,
        TypeFactory $typeFactory
    ) {
        $this->typeHandlerFactory = $typeHandlerFactory;
        $this->typeFactory = $typeFactory;
    }

    /**
     * Get a Type
     *
     * @param string $typeName
     * @return TypeInterface
     * @throws \LogicException
     */
    public function getType(string $typeName)
    {
        if (isset($this->typeRegistry[$typeName])) {
            return $this->typeRegistry[$typeName];
        }

        if ($this->isScalar($typeName)) {
            $this->typeRegistry[$typeName] = $this->typeFactory->createScalar($typeName);
            return $this->typeRegistry[$typeName];
        } else {
            return $this->getComplexType($typeName);
        }
    }

    /**
     * Retrieve type's configuration based off name
     *
     * @param string $typeName
     * @return TypeInterface|null
     * @throws \LogicException Type Handler could not be found, and type does not exist in registry
     */
    public function getComplexType(string $typeName)
    {
        if (isset($this->typeRegistry[$typeName])) {
            return $this->typeRegistry[$typeName];
        }
        $typeHandlerName = __NAMESPACE__ . '\\'. $typeName;
        if (!class_exists($typeHandlerName)) {
            throw new \LogicException(sprintf('Type handler not implemented for %s', $typeHandlerName));
        }

        $typeHandler = $this->typeHandlerFactory->create($typeHandlerName);

        $this->typeRegistry[$typeName] = $typeHandler->getType();
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
