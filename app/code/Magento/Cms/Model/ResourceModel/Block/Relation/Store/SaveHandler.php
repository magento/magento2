<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\ResourceModel\Block\Relation\Store;

use Magento\Cms\Model\ResourceModel\Block;
use Magento\Framework\Model\Entity\MetadataPool;

class SaveHandler
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var Block
     */
    protected $resourceBlock;

    /**
     * @param MetadataPool $metadataPool
     * @param Block $resourceBlock
     */
    public function __construct(
        MetadataPool $metadataPool,
        Block $resourceBlock
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceBlock = $resourceBlock;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return object
     */
    public function execute($entityType, $entity)
    {
        $entityMetadata = $this->metadataPool->getMetadata($entityType);
        $linkField = $entityMetadata->getLinkField();

        $connection = $entityMetadata->getEntityConnection();

        $oldStores = $this->resourceBlock->lookupStoreIds((int)$entity->getId());
        $newStores = (array)$entity->getStores();

        $table = $this->resourceBlock->getTable('cms_block_store');

        $delete = array_diff($oldStores, $newStores);
        if ($delete) {
            $where = [
                $linkField . ' = ?' => (int)$entity->getData($linkField),
                'store_id IN (?)' => $delete,
            ];
            $connection->delete($table, $where);
        }

        $insert = array_diff($newStores, $oldStores);
        if ($insert) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = [
                    $linkField => (int)$entity->getData($linkField),
                    'store_id' => (int)$storeId,
                ];
            }
            $connection->insertMultiple($table, $data);
        }

        return $entity;
    }
}
