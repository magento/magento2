<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\ResourceModel\Db;

use Magento\Framework\EntityManager\MetadataPool;

/**
 * Delete entity row.
 */
class DeleteEntityRow
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        MetadataPool $metadataPool
    ) {
        $this->metadataPool = $metadataPool;
    }

    /**
     * Delete entity row.
     *
     * @param string $entityType
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    public function execute($entityType, $data)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $connection = $metadata->getEntityConnection();
        return $connection->delete(
            $metadata->getEntityTable(),
            [$metadata->getLinkField() . ' = ?' => $data[$metadata->getLinkField()]]
        );
    }
}
