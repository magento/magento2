<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class MapperPool
 */
class MapperPool
{
    /**
     * @var string[]
     */
    private $mappers;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string[] $mappers
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
     */
    public function getMapper($entityType)
    {
        $className = isset($this->mappers[$entityType]) ? $this->mappers[$entityType] : MapperInterface::class;
        return $this->objectManager->get($className);
    }
}
