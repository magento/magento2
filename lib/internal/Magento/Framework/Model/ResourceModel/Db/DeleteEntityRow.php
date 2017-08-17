<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\ResourceModel\Db;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\EntityMetadata;

/**
 * Class \Magento\Framework\Model\ResourceModel\Db\DeleteEntityRow
 *
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
