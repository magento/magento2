<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentCatalog\Observer;

use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\MediaContentApi\Api\UpdateContentAssetLinksInterface;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterfaceFactory;
use Magento\MediaContentApi\Model\GetEntityContentsInterface;
use Magento\MediaContentApi\Model\GetCustomAttributesContentInterface;

/**
 * Observe the catalog_product_save_after event and run processing relation between product content and media asset
 */
class Product implements ObserverInterface
{
    private const CONTENT_TYPE = 'catalog_product';
    private const TYPE = 'entityType';
    private const ENTITY_ID = 'entityId';
    private const FIELD = 'field';
    private const CUSTOM_ATTRIBUTES_FIELD = 'product_custom_attribute';

    /**
     * @var UpdateContentAssetLinksInterface
     */
    private $updateContentAssetLinks;

    /**
     * @var array
     */
    private $fields;

    /**
     * @var ContentIdentityInterfaceFactory
     */
    private $contentIdentityFactory;

    /**
     * @var GetEntityContentsInterface
     */
    private $getContent;

    /**
     * @var GetCustomAttributesContentInterface
     */
    private $getCustomAttributesContent;
    
    /**
     * @param GetCustomAttributesContentInterface $getCustomAttributesContent
     * @param ContentIdentityInterfaceFactory $contentIdentityFactory
     * @param GetEntityContentsInterface $getContent
     * @param UpdateContentAssetLinksInterface $updateContentAssetLinks
     * @param array $fields
     */
    public function __construct(
        GetCustomAttributesContentInterface $getCustomAttributesContent,
        ContentIdentityInterfaceFactory $contentIdentityFactory,
        GetEntityContentsInterface $getContent,
        UpdateContentAssetLinksInterface $updateContentAssetLinks,
        array $fields
    ) {
        $this->getCustomAttributesContents = $getCustomAttributesContent;
        $this->contentIdentityFactory = $contentIdentityFactory;
        $this->getContent = $getContent;
        $this->updateContentAssetLinks = $updateContentAssetLinks;
        $this->fields = $fields;
    }

    /**
     * Retrieve the saved product and pass it to the model processor to save content - asset relations
     *
     * @param Observer $observer
     * @throws \Exception
     */
    public function execute(Observer $observer): void
    {
        $model = $observer->getEvent()->getData('product');
        if ($model instanceof CatalogProduct) {
            $this->updateCustomAttributes((int) $model->getEntityId());
            foreach ($this->fields as $field) {
                if (!$model->dataHasChangedFor($field)) {
                    continue;
                }
                $contentIdentity = $this->contentIdentityFactory->create(
                    [
                        self::TYPE => self::CONTENT_TYPE,
                        self::FIELD => $field,
                        self::ENTITY_ID => (string) $model->getEntityId(),
                    ]
                );
                $concatenatedContent = implode(PHP_EOL, $this->getContent->execute($contentIdentity));
                $this->updateContentAssetLinks->execute($contentIdentity, $concatenatedContent);
            }
        }
    }
    
    /**
     * Update custom product attributes fields.
     *
     * @param int $entityId
     */
    private function updateCustomAttributes(int $entityId): void
    {
        $contentIdentity = $this->contentIdentityFactory->create(
            [
                    self::TYPE => self::CONTENT_TYPE,
                    self::FIELD => self::CUSTOM_ATTRIBUTES_FIELD,
                    self::ENTITY_ID => $entityId
                ]
        );
        $this->updateContentAssetLinks->execute(
            $contentIdentity,
            implode(
                PHP_EOL,
                $this->getCustomAttributesContents->execute(self::CONTENT_TYPE, (int) $entityId)
            )
        );
    }
}
