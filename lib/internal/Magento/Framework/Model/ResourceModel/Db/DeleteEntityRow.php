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
 * @since 2.1.0
 */
class DeleteEntityRow
{
    /**
     * @var MetadataPool
     * @since 2.1.0
     */
    protected $metadataPool;

    /**
     * @param MetadataPool $metadataPool
     * @since 2.1.0
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
     * @since 2.1.0
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
