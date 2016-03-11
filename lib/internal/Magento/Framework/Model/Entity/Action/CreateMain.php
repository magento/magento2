<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Entity\Action;

use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\CreateEntityRow;

/**
 * Class CreateMain
 */
class CreateMain
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var CreateEntityRow
     */
    protected $createEntityRow;

    /**
     * @param MetadataPool $metadataPool
     * @param CreateEntityRow $createEntityRow
     */
    public function __construct(
        MetadataPool $metadataPool,
        CreateEntityRow $createEntityRow
    ) {
        $this->metadataPool = $metadataPool;
        $this->createEntityRow = $createEntityRow;
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
        $entityData = $this->createEntityRow->execute(
            $entityType,
            array_merge($hydrator->extract($entity), $data)
        );
        $entity = $hydrator->hydrate($entity, $entityData);
        return $entity;
    }
}
