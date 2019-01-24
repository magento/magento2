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
use Magento\Framework\Search\Adapter\Mysql\Filter\Builder\Term;

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
    private $productUrlRewriteGenerator;

    /**
     * @var UrlPersistInterface
     */
    private $urlPersist;

    /**
     * @var ProductUrlPathGenerator
     */
    private $productUrlPathGenerator;

    /**
     * @param CollectionFactory $collectionFactory
     * @param ProductUrlRewriteGenerator $productUrlRewriteGenerator
     * @param UrlPersistInterface $urlPersist
     * @param ProductUrlPathGenerator|null $productUrlPathGenerator
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        UrlPersistInterface $urlPersist,
        ProductUrlPathGenerator $productUrlPathGenerator
    ) {
        $this->productCollectionFactory = $collectionFactory;
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
        $this->productUrlPathGenerator = $productUrlPathGenerator;
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
            [Term::CONDITION_OPERATOR_IN => array_unique($productIds)]
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
                $productUrlPathGenerator = $this->productUrlPathGenerator->getUrlPath($product);
                $productUrlRewriteGenerator = $this->productUrlRewriteGenerator->generate($product);
                $product->unsUrlPath();
                $product->setUrlPath($productUrlPathGenerator);
                $this->urlPersist->replace($productUrlRewriteGenerator);
            }
        }

        return [$attrData, $productIds, $storeId];
    }
}
