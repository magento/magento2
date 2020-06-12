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

/**
 * Observe the catalog_product_save_after event and run processing relation between product content and media asset
 */
class Product implements ObserverInterface
{
    private const CONTENT_TYPE = 'catalog_product';
    private const TYPE = 'entityType';
    private const ENTITY_ID = 'entityId';
    private const FIELD = 'field';
    private const CUSTOM_ATTRIBUTES_FIELD = 'custom_attributes';
    private const CUSTOM_ATTRIBUTES_CONTENT_TYPE = 'catalog_product_custom_attributes';

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
     * @param ContentIdentityInterfaceFactory $contentIdentityFactory
     * @param GetEntityContentsInterface $getContent
     * @param UpdateContentAssetLinksInterface $updateContentAssetLinks
     * @param array $fields
     */
    public function __construct(
        ContentIdentityInterfaceFactory $contentIdentityFactory,
        GetEntityContentsInterface $getContent,
        UpdateContentAssetLinksInterface $updateContentAssetLinks,
        array $fields
    ) {
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
            foreach ($this->fields as $field) {
                if ($field !== self::CUSTOM_ATTRIBUTES_FIELD && !$model->dataHasChangedFor($field)) {
                    continue;
                }
                $contentIdentity = $this->contentIdentityFactory->create(
                    [
                        self::TYPE => $this->getEntityType($field),
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
     * Return content_type by field
     *
     * @param string $field
     */
    private function getEntityType(string $field): string
    {
        return $field === self::CUSTOM_ATTRIBUTES_FIELD ?
                      self::CUSTOM_ATTRIBUTES_CONTENT_TYPE :
                      self::CONTENT_TYPE;
    }
}
