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
use Magento\SalesSequence\Model\ResourceModel\Profile as ResourceProfile;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Class DeleteByStore
 * @api
 */
class DeleteByStore
{
    /**
     * @var resourceMetadata
     */
    protected $resourceMetadata;

    /**
     * @var ResourceProfile
     */
    private $resourceProfile;

    /**
     * @var MetaFactory
     */
    protected $metaFactory;

    /**
     * @var AppResource
     */
    protected $appResource;

    /**
     * @param ResourceMetadata $resourceMetadata
     * @param ResourceProfile $resourceProfile
     * @param MetaFactory $metaFactory
     * @param AppResource $appResource
     */
    public function __construct(
        ResourceMetadata $resourceMetadata,
        ResourceProfile $resourceProfile,
        MetaFactory $metaFactory,
        AppResource $appResource
    ) {
        $this->resourceMetadata = $resourceMetadata;
        $this->resourceProfile = $resourceProfile;
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
        $metadataIds = $this->resourceMetadata->getIdsByStore($store->getId());
        $profileIds = $this->resourceProfile->getProfileIdsByMetadataIds($metadataIds);

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
