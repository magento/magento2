<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class ProductProcessUrlRewriteSavingObserver
 */
class ProcessUrlRewriteOnChangeProductVisibilityObserver implements ObserverInterface
{
    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var ProductUrlRewriteGenerator
     */
    private $urlRewriteGenerator;

    /**
     * @var UrlPersistInterface
     */
    private $urlPersist;

    /**
     * @var ProductUrlPathGenerator
     */
    private $urlPathGenerator;

    /**
     * @param CollectionFactory $collectionFactory
     * @param ProductUrlRewriteGenerator $urlRewriteGenerator
     * @param UrlPersistInterface $urlPersist
     * @param ProductUrlPathGenerator|null $urlPathGenerator
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ProductUrlRewriteGenerator $urlRewriteGenerator,
        UrlPersistInterface $urlPersist,
        ProductUrlPathGenerator $urlPathGenerator
    ) {
        $this->productCollectionFactory = $collectionFactory;
        $this->urlRewriteGenerator = $urlRewriteGenerator;
        $this->urlPersist = $urlPersist;
        $this->urlPathGenerator = $urlPathGenerator;
    }

    /**
     * Generate urls for UrlRewrites and save it in storage
     *
     * @param Observer $observer
     * @return array
     * @throws UrlAlreadyExistsException
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        $attrData = $event->getAttributesData();
        $productIds = $event->getProductIds();
        $storeId = $event->getStoreId();
        $visibility = $attrData[ProductInterface::VISIBILITY] ?? null;

        if (!$visibility) {
            return [$attrData, $productIds, $storeId];
        }

        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addAttributeToSelect(ProductInterface::VISIBILITY);
        $productCollection->addAttributeToSelect('url_key');
        $productCollection->addFieldToFilter(
            'entity_id',
            ['in' => array_unique($productIds)]
        );

        foreach ($productCollection as $product) {
            if ($visibility == Visibility::VISIBILITY_NOT_VISIBLE) {
                $this->urlPersist->deleteByData(
                    [
                        UrlRewrite::ENTITY_ID => $product->getId(),
                        UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                    ]
                );
            } elseif ($visibility !== Visibility::VISIBILITY_NOT_VISIBLE) {
                $product->setVisibility($visibility);
                $productUrlPath = $this->urlPathGenerator->getUrlPath($product);
                $productUrlRewrite = $this->urlRewriteGenerator->generate($product);
                $product->unsUrlPath();
                $product->setUrlPath($productUrlPath);
                $this->urlPersist->replace($productUrlRewrite);
            }
        }

        return [$attrData, $productIds, $storeId];
    }
}
