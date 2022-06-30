<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronizationCatalog\Model\Synchronizer;

use Magento\MediaContentApi\Api\Data\ContentIdentityInterfaceFactory;
use Magento\MediaContentApi\Api\UpdateContentAssetLinksInterface;
use Magento\MediaContentApi\Model\GetEntityContentsInterface;
use Magento\MediaContentSynchronizationApi\Api\SynchronizeInterface;
use Magento\MediaGallerySynchronizationApi\Model\FetchBatchesInterface;

/**
 * Synchronize category content with assets
 */
class Category implements SynchronizeInterface
{
    private const CONTENT_TYPE = 'catalog_category';
    private const TYPE = 'entityType';
    private const ENTITY_ID = 'entityId';
    private const FIELD = 'field';
    private const CATEGORY_TABLE = 'catalog_category_entity';
    private const CATEGORY_IDENTITY_FIELD = 'entity_id';
    private const CATEGORY_UPDATED_AT_FIELD = 'updated_at';

    /**
     * @var UpdateContentAssetLinksInterface
     */
    private $updateContentAssetLinks;

    /**
     * @var ContentIdentityInterfaceFactory
     */
    private $contentIdentityFactory;

    /**
     * @var GetEntityContentsInterface
     */
    private $getEntityContents;

    /**
     * @var FetchBatchesInterface
     */
    private $fetchBatches;

    /**
     * @var array
     */
    private $fields;

    /**
     * @param ContentIdentityInterfaceFactory $contentIdentityFactory
     * @param GetEntityContentsInterface $getEntityContents
     * @param UpdateContentAssetLinksInterface $updateContentAssetLinks
     * @param FetchBatchesInterface $fetchBatches
     * @param array $fields
     */
    public function __construct(
        ContentIdentityInterfaceFactory $contentIdentityFactory,
        GetEntityContentsInterface $getEntityContents,
        UpdateContentAssetLinksInterface $updateContentAssetLinks,
        FetchBatchesInterface $fetchBatches,
        array $fields = []
    ) {
        $this->contentIdentityFactory = $contentIdentityFactory;
        $this->getEntityContents = $getEntityContents;
        $this->updateContentAssetLinks = $updateContentAssetLinks;
        $this->fields = $fields;
        $this->fetchBatches = $fetchBatches;
    }

    /**
     * @inheritdoc
     */
    public function execute(): void
    {
        $columns = [
            self::CATEGORY_IDENTITY_FIELD,
            self::CATEGORY_UPDATED_AT_FIELD
        ];
        foreach ($this->fetchBatches->execute(self::CATEGORY_TABLE, $columns, $columns[1]) as $batch) {
            foreach ($batch as $item) {
                $this->synchronizeItem($item);
            }
        }
    }

    /**
     * Synchronize product entity fields
     *
     * @param array $item
     */
    private function synchronizeItem(array $item): void
    {
        foreach ($this->fields as $field) {
            $contentIdentity = $this->contentIdentityFactory->create(
                [
                    self::TYPE => self::CONTENT_TYPE,
                    self::FIELD => $field,
                    self::ENTITY_ID => $item[self::CATEGORY_IDENTITY_FIELD]
                ]
            );
            $this->updateContentAssetLinks->execute(
                $contentIdentity,
                implode(PHP_EOL, $this->getEntityContents->execute($contentIdentity))
            );
        }
    }
}
