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
use Magento\MediaContentCatalog\Model\ResourceModel\GetProductContent;
use Magento\Eav\Model\Config;

/**
 * Observe the catalog_product_save_after event and run processing relation between product content and media asset
 */
class Product implements ObserverInterface
{
    private const CONTENT_TYPE = 'catalog_product';
    private const TYPE = 'entityType';
    private const ENTITY_ID = 'entityId';
    private const FIELD = 'field';

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
     * @var GetProductContent
     */
    private $getContent;

    /**
     * @var Config
     */
    private $config;

    /**
     * * Create links for product content
     *
     * @param ContentIdentityInterfaceFactory $contentIdentityFactory
     * @param GetProductContent $getContent
     * @param UpdateContentAssetLinksInterface $updateContentAssetLinks
     * @param Config $config
     * @param array $fields
     */
    public function __construct(
        ContentIdentityInterfaceFactory $contentIdentityFactory,
        GetProductContent $getContent,
        UpdateContentAssetLinksInterface $updateContentAssetLinks,
        Config $config,
        array $fields
    ) {
        $this->contentIdentityFactory = $contentIdentityFactory;
        $this->getContent = $getContent;
        $this->updateContentAssetLinks = $updateContentAssetLinks;
        $this->config = $config;
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
                if (!$model->dataHasChangedFor($field)) {
                    continue;
                }
                $attribute = $this->config->getAttribute(self::CONTENT_TYPE, $field);
                $this->updateContentAssetLinks->execute(
                    $this->contentIdentityFactory->create(
                        [
                            self::TYPE => self::CONTENT_TYPE,
                            self::FIELD => $field,
                            self::ENTITY_ID => (string) $model->getEntityId(),
                        ]
                    ),
                    $this->getContent->execute((int) $model->getEntityId(), $attribute)
                );
            }
        }
    }
}
