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
use Magento\MediaContentApi\Api\Data\ContentIdentityInterfaceFactory;
use Magento\MediaContentApi\Api\Data\ContentAssetLinkInterfaceFactory;
use Magento\MediaContentApi\Api\DeleteContentAssetLinksInterface;
use Magento\MediaContentApi\Model\GetEntityContentsInterface;
use Magento\MediaContentApi\Api\ExtractAssetsFromContentInterface;

/**
 * Observe the catalog_product_delete_before event and deletes relation between category content and media asset.
 */
class ProductDelete implements ObserverInterface
{
    private const CONTENT_TYPE = 'catalog_product';
    private const TYPE = 'entityType';
    private const ENTITY_ID = 'entityId';
    private const FIELD = 'field';
    
    /**
     * @var ContentIdentityInterfaceFactory
     */
    private $contentIdentityFactory;

    /**
     * @var ContentAssetLinkInterfaceFactory
     */
    private $contentAssetLinkFactory;

    /**
     * @var DeleteContentAssetLinksInterface
     */
    private $deleteContentAssetLinks;

    /**
     * @var array
     */
    private $fields;

    /**
     * @var GetEntityContentsInterface
     */
    private $getContent;

    /**
     * @var ExtractAssetsFromContentInterface
     */
    private $extractAssetsFromContent;
    
    /**
     * @param ExtractAssetsFromContentInterface $extractAssetsFromContent
     * @param GetEntityContentsInterface $getContent
     * @param DeleteContentAssetLinksInterface $deleteContentAssetLinks
     * @param ContentIdentityInterfaceFactory $contentIdentityFactory
     * @param ContentAssetLinkInterfaceFactory $contentAssetLinkFactory
     * @param array $fields
     */
    public function __construct(
        ExtractAssetsFromContentInterface $extractAssetsFromContent,
        GetEntityContentsInterface $getContent,
        DeleteContentAssetLinksInterface $deleteContentAssetLinks,
        ContentIdentityInterfaceFactory $contentIdentityFactory,
        ContentAssetLinkInterfaceFactory $contentAssetLinkFactory,
        array $fields
    ) {
        $this->extractAssetsFromContent = $extractAssetsFromContent;
        $this->getContent = $getContent;
        $this->deleteContentAssetLinks = $deleteContentAssetLinks;
        $this->contentAssetLinkFactory = $contentAssetLinkFactory;
        $this->contentIdentityFactory = $contentIdentityFactory;
        $this->fields = $fields;
    }

    /**
     * Retrieve the deleted product and  remove relation betwen product and asset
     *
     * @param Observer $observer
     * @throws \Exception
     */
    public function execute(Observer $observer): void
    {
        $model = $observer->getEvent()->getData('product');
        $contentAssetLinks = [];
        
        if ($model instanceof CatalogProduct) {
            foreach ($this->fields as $field) {
                $contentIdentity = $this->contentIdentityFactory->create(
                    [
                        self::TYPE => self::CONTENT_TYPE,
                        self::FIELD => $field,
                        self::ENTITY_ID => (string) $model->getEntityId(),
                    ]
                );
                $content = implode(PHP_EOL, $this->getContent->execute($contentIdentity));
                $assets = $this->extractAssetsFromContent->execute($content);

                foreach ($assets as $asset) {
                    $contentAssetLinks[] = $this->contentAssetLinkFactory->create(
                        [
                            'assetId' => $asset->getId(),
                            'contentIdentity' => $contentIdentity
                        ]
                    );
                }
            }
            if (!empty($contentAssetLinks)) {
                $this->deleteContentAssetLinks->execute($contentAssetLinks);
            }
        }
    }
}
