<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesSequence\Model\Sequence;

use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\SalesSequence\Model\MetaFactory;
use Magento\SalesSequence\Model\ResourceModel\Meta as ResourceMetadata;

/**
 * Delete Sequence by Store.
 */
class DeleteByStore
{
    /**
     * @var ResourceMetadata
     */
    private $resourceMetadata;

    /**
     * @var MetaFactory
     */
    private $metaFactory;

    /**
     * @var AppResource
     */
    private $appResource;

    /**
     * @param ResourceMetadata $resourceMetadata
     * @param MetaFactory $metaFactory
     * @param AppResource $appResource
     */
    public function __construct(
        ResourceMetadata $resourceMetadata,
        MetaFactory $metaFactory,
        AppResource $appResource
    ) {
        $this->resourceMetadata = $resourceMetadata;
        $this->metaFactory = $metaFactory;
        $this->appResource = $appResource;
    }

    /**
     * Deletes all sequence linked entites
     *
     * @param int $storeId
     * @return void
     * @throws \Exception
     */
    public function execute($storeId): void
    {
        $metadataIds = $this->getMetadataIdsByStoreId($storeId);
        $profileIds = $this->getProfileIdsByMetadataIds($metadataIds);

        $this->appResource->getConnection('sales')->delete(
            $this->appResource->getTableName('sales_sequence_profile'),
            ['profile_id IN (?)' => $profileIds]
        );

        foreach ($metadataIds as $metadataId) {
            $metadata = $this->metaFactory->create();
            $this->resourceMetadata->load($metadata, $metadataId);
            if (!$metadata->getId()) {
                continue;
            }

            $this->appResource->getConnection('sales')->dropTable(
                $metadata->getSequenceTable()
            );
            $this->resourceMetadata->delete($metadata);
        }
    }

    /**
     * Retrieves Metadata Ids by store id
     *
     * @param int $storeId
     * @return int[]
     */
    private function getMetadataIdsByStoreId($storeId)
    {
        $connection = $this->appResource->getConnection('sales');
        $bind = ['store_id' => $storeId];
        $select = $connection->select()->from(
            $this->appResource->getTableName('sales_sequence_meta'),
            ['meta_id']
        )->where(
            'store_id = :store_id'
        );

        return $connection->fetchCol($select, $bind);
    }

    /**
     * Retrieves Profile Ids by metadata ids
     *
     * @param int[] $metadataIds
     * @return int[]
     */
    private function getProfileIdsByMetadataIds(array $metadataIds)
    {
        $connection = $this->appResource->getConnection('sales');
        $select = $connection->select()
            ->from(
                $this->appResource->getTableName('sales_sequence_profile'),
                ['profile_id']
            )->where('meta_id IN (?)', $metadataIds);

        return $connection->fetchCol($select);
    }
}
