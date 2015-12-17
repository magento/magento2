<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Entity\Action;

use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\UpdateEntityRow;

/**
 * Class UpdateMain
 */
class UpdateMain
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var UpdateEntityRow
     */
    protected $updateEntityRow;

    /**
     * @param MetadataPool $metadataPool
     * @param UpdateEntityRow $updateEntityRow
     */
    public function __construct(
        MetadataPool $metadataPool,
        UpdateEntityRow $updateEntityRow
    ) {
        $this->metadataPool = $metadataPool;
        $this->updateEntityRow = $updateEntityRow;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @param array $data
     * @return object
     */
    public function execute($entityType, $entity, $data = [])
    {
        $hydrator = $this->metadataPool->getHydrator($entityType);
        $entityData = array_merge($hydrator->extract($entity), $data);
        $this->updateEntityRow->execute($entityType, $entityData);
        $hydrator->hydrate($entity, $entityData);
        return $entity;
    }
}
