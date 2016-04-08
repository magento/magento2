<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Operation\Write\Update;

use Magento\Framework\EntityManager\HydratorPool;
use Magento\Framework\EntityManager\Db\UpdateRow;

/**
 * Class UpdateMain
 */
class UpdateMain
{
    /**
     * @var HydratorPool
     */
    private $hydratorPool;

    /**
     * @var UpdateRow
     */
    private $updateRow;

    /**
     * UpdateMain constructor.
     *
     * @param HydratorPool $hydratorPool
     * @param UpdateRow $updateRow
     */
    public function __construct(
        HydratorPool $hydratorPool,
        UpdateRow $updateRow
    ) {
        $this->hydratorPool = $hydratorPool;
        $this->updateRow = $updateRow;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @param array $arguments
     * @return object
     */
    public function execute($entityType, $entity, $arguments = [])
    {
        $hydrator = $this->hydratorPool->getHydrator($entityType);
        $entityData = $this->updateRow->execute(
            $entityType,
            array_merge($hydrator->extract($entity), $arguments)
        );
        $entity = $hydrator->hydrate($entity, $entityData);
        return $entity;
    }
}
