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
use Magento\SalesSequence\Model\ResourceModel\Meta\Ids as ResourceMetadataIds;
use Magento\SalesSequence\Model\ResourceModel\Profile\Ids as ResourceProfileIds;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Class DeleteByStore
 */
class DeleteByStore
{
    /**
     * @var ResourceMetadata
     */
    private $resourceMetadata;

    /**
     * @var ResourceMetadataIds
     */
    private $resourceMetadataIds;

    /**
     * @var ResourceProfileIds
     */
    private $resourceProfileIds;

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
     * @param ResourceMetadataIds $resourceMetadataIds
     * @param ResourceProfileIds $resourceProfileIds
     * @param MetaFactory $metaFactory
     * @param AppResource $appResource
     */
    public function __construct(
        ResourceMetadata $resourceMetadata,
        ResourceMetadataIds $resourceMetadataIds,
        ResourceProfileIds $resourceProfileIds,
        MetaFactory $metaFactory,
        AppResource $appResource
    ) {
        $this->resourceMetadata = $resourceMetadata;
        $this->resourceMetadataIds = $resourceMetadataIds;
        $this->resourceProfileIds = $resourceProfileIds;
        $this->metaFactory = $metaFactory;
        $this->appResource = $appResource;
    }

    /**
     * Deletes all sequence linked entites
     *
     * @param StoreInterface $store
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(StoreInterface $store): void
    {
        $metadataIds = $this->resourceMetadataIds->getByStoreId($store->getId());
        $profileIds = $this->resourceProfileIds->getByMetadataIds($metadataIds);

        $this->appResource->getConnection()->delete(
            $this->appResource->getTableName('sales_sequence_profile'),
            ['profile_id IN (?)' => $profileIds]
        );

        foreach ($metadataIds as $metadataId) {
            $metadata = $this->metaFactory->create();
            $this->resourceMetadata->load($metadata, $metadataId);
            if (!$metadata->getId()) {
                continue;
            }

            $this->appResource->getConnection()->dropTable(
                $metadata->getSequenceTable()
            );
            $this->resourceMetadata->delete($metadata);
        }
    }
}
