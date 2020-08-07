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
use Magento\MediaContentSynchronizationApi\Api\SynchronizerInterface;
use Magento\MediaGallerySynchronizationApi\Model\FetchBatchesInterface;

/**
 * Synchronize product content with assets
 */
class Product implements SynchronizerInterface
{
    private const CONTENT_TYPE = 'catalog_product';
    private const TYPE = 'entityType';
    private const ENTITY_ID = 'entityId';
    private const FIELD = 'field';
    private const PRODUCT_TABLE = 'catalog_product_entity';
    private const PRODUCT_TABLE_ENTITY_ID = 'entity_id';
    private const PRODUCT_TABLE_UPDATED_AT_FIELD = 'updated_at';

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
     * @var array
     */
    private $fields;

    /**
     * @var FetchBatchesInterface
     */
    private $fetchBatches;

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
        $this->fetchBatches = $fetchBatches;
        $this->fields = $fields;
    }

    /**
     * @inheritdoc
     */
    public function execute(): void
    {
        $columns = [self::PRODUCT_TABLE_ENTITY_ID, self::PRODUCT_TABLE_UPDATED_AT_FIELD];
        foreach ($this->fetchBatches->execute(self::PRODUCT_TABLE, $columns, $columns[1]) as $batch) {
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
                    self::ENTITY_ID => $item[self::PRODUCT_TABLE_ENTITY_ID]
                ]
            );
            $this->updateContentAssetLinks->execute(
                $contentIdentity,
                implode(PHP_EOL, $this->getEntityContents->execute($contentIdentity))
            );
        }
    }
}
