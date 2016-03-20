<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Operation\Write\Delete;

use Magento\Framework\EntityManager\HydratorPool;
use Magento\Framework\EntityManager\Db\DeleteRow;

/**
 * Class DeleteMain
 */
class DeleteMain
{
    /**
     * @var HydratorPool
     */
    private $hydratorPool;

    /**
     * @var DeleteRow
     */
    private $deleteRow;

    /**
     * DeleteMain constructor.
     *
     * @param HydratorPool $hydratorPool
     * @param DeleteRow $deleteRow
     */
    public function __construct(
        HydratorPool $hydratorPool,
        DeleteRow $deleteRow
    ) {
        $this->hydratorPool = $hydratorPool;
        $this->deleteRow = $deleteRow;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return int
     */
    public function execute($entityType, $entity)
    {
        $hydrator = $this->hydratorPool->getHydrator($entityType);
        return $this->deleteRow->execute($entityType, $hydrator->extract($entity));
    }
}
