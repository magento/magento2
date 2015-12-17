<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Entity\Action;

use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\DeleteEntityRow;

/**
 * Class DeleteMain
 */
class DeleteMain
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var DeleteEntityRow
     */
    protected $deleteEntityRow;

    /**
     * @param MetadataPool $metadataPool
     * @param DeleteEntityRow $createEntityRow
     */
    public function __construct(
        MetadataPool $metadataPool,
        DeleteEntityRow $createEntityRow
    ) {
        $this->metadataPool = $metadataPool;
        $this->deleteEntityRow = $createEntityRow;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return bool
     */
    public function execute($entityType, $entity)
    {
        $hydrator = $this->metadataPool->getHydrator($entityType);
        $entityData = $hydrator->extract($entity);
        return $this->deleteEntityRow->execute(
            $entityType,
            $entityData
        );
    }
}
