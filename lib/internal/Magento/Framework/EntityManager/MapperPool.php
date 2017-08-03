<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class MapperPool
 * @since 2.1.0
 */
class MapperPool
{
    /**
     * @var string[]
     * @since 2.1.0
     */
    private $mappers;

    /**
     * @var ObjectManagerInterface
     * @since 2.1.0
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string[] $mappers
     * @since 2.1.0
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $mappers = []
    ) {
        $this->objectManager = $objectManager;
        $this->mappers = $mappers;
    }

    /**
     * Get mapper for entity type
     * @param string $entityType
     * @return MapperInterface
     * @since 2.1.0
     */
    public function getMapper($entityType)
    {
        $className = isset($this->mappers[$entityType]) ? $this->mappers[$entityType] : MapperInterface::class;
        return $this->objectManager->get($className);
    }
}
