<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type\Handler;

use GraphQL\Type\Definition\ObjectType;
use \GraphQL\Type\Definition\Type;
use Magento\GraphQl\Model\Type\HandlerFactory;

/**
 * Retrieve type's registered in pool, or generate types yet to be instantiated and register them
 */
class Pool
{
    /**
     * @var HandlerFactory
     */
    private $typeHandlerFactory;

    /**
     * @var Type[]
     */
    private $typeRegistry = [];

    /**
     * @param HandlerFactory $typeHandlerFactory
     */
    public function __construct(\Magento\GraphQl\Model\Type\HandlerFactory $typeHandlerFactory)
    {
        $this->typeHandlerFactory = $typeHandlerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(string $typeName)
    {
        if ($type = $this->mapScalarType($typeName)) {
            return $type;
        }

        if ($type = $this->getComplexType($typeName)) {
            return $type;
        }

        throw new \LogicException(sprintf('%s type could not be resolved or generated.', $typeName));
    }

    /**
     * Retrieve type's configuration based off name
     *
     * @param string $typeName
     * @return Type|null
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
     * @param Type $type
     * @throws \LogicException
     */
    public function registerType(Type $type)
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
     * Map type name to scalar GraphQL type, otherwise return null
     *
     * @param string $typeName
     * @return Type|null
     */
    private function mapScalarType($typeName)
    {
        $scalarTypes = $this->getInternalTypes();

        return isset($scalarTypes[$typeName]) ? $scalarTypes[$typeName] : null;
    }

    /**
     * Get all internal scalar types
     *
     * @return array
     */
    private function getInternalTypes()
    {
        // TODO: Fix this when creating new wrappers for webonyx. This is only a temporary workaround for static tests.
        $object = new ObjectType(['name' => 'fake', 'fields' => 'fake']);
        return $object->getInternalTypes();
    }
}
