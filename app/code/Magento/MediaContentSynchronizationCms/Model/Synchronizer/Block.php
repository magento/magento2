<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronizationCms\Model\Synchronizer;

use Magento\MediaContentApi\Api\Data\ContentIdentityInterfaceFactory;
use Magento\MediaContentApi\Api\UpdateContentAssetLinksInterface;
use Magento\MediaContentSynchronizationApi\Api\SynchronizeInterface;
use Magento\MediaGallerySynchronizationApi\Model\FetchBatchesInterface;

/**
 * Synchronize block content with assets
 */
class Block implements SynchronizeInterface
{
    private const CONTENT_TYPE = 'cms_block';
    private const TYPE = 'entityType';
    private const ENTITY_ID = 'entityId';
    private const FIELD = 'field';
    private const CMS_BLOCK_TABLE = 'cms_block';
    private const CMS_BLOCK_TABLE_ENTITY_ID = 'block_id';
    private const CMS_BLOCK_TABLE_UPDATED_AT_FIELD = 'update_time';

    /**
     * @var FetchBatchesInterface
     */
    private $fetchBatches;

    /**
     * @var UpdateContentAssetLinksInterface
     */
    private $updateContentAssetLinks;

    /**
     * @var ContentIdentityInterfaceFactory
     */
    private $contentIdentityFactory;

    /**
     * @var array
     */
    private $fields;

    /**
     * Synchronize block content with assets
     *
     * @param ContentIdentityInterfaceFactory $contentIdentityFactory
     * @param UpdateContentAssetLinksInterface $updateContentAssetLinks
     * @param FetchBatchesInterface $fetchBatches
     * @param array $fields
     */
    public function __construct(
        ContentIdentityInterfaceFactory $contentIdentityFactory,
        UpdateContentAssetLinksInterface $updateContentAssetLinks,
        FetchBatchesInterface $fetchBatches,
        array $fields = []
    ) {
        $this->contentIdentityFactory = $contentIdentityFactory;
        $this->updateContentAssetLinks = $updateContentAssetLinks;
        $this->fields = $fields;
        $this->fetchBatches = $fetchBatches;
    }

    /**
     * Synchronize assets and contents
     */
    public function execute(): void
    {
        $columns =  array_merge(
            [
                self::CMS_BLOCK_TABLE_ENTITY_ID,
                self::CMS_BLOCK_TABLE_UPDATED_AT_FIELD
            ],
            array_values($this->fields)
        );
        foreach ($this->fetchBatches->execute(self::CMS_BLOCK_TABLE, $columns, $columns[1]) as $batch) {
            foreach ($batch as $item) {
                $this->synchronizeItem($item);
            }
        }
    }

    /**
     * Synchronize block entity fields
     *
     * @param array $item
     */
    private function synchronizeItem(array $item): void
    {
        foreach ($this->fields as $field) {
            $this->updateContentAssetLinks->execute(
                $this->contentIdentityFactory->create(
                    [
                        self::TYPE => self::CONTENT_TYPE,
                        self::FIELD => $field,
                        self::ENTITY_ID => $item[self::CMS_BLOCK_TABLE_ENTITY_ID]
                    ]
                ),
                (string) $item[$field]
            );
        }
    }
}
