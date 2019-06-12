<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\GetStoresWithDefaultValuesUsed;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ObserverInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Class ProductProcessUrlRewriteSavingObserver
 */
class ProductProcessUrlRewriteSavingObserver implements ObserverInterface
{
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
     * @var GetStoresWithDefaultValuesUsed
     */
    private $getStoresWithDefaultValuesUsed;

    /**
     * @param ProductUrlRewriteGenerator $productUrlRewriteGenerator
     * @param UrlPersistInterface $urlPersist
     * @param ProductUrlPathGenerator|null $productUrlPathGenerator
     * @param GetStoresWithDefaultValuesUsed|null $getStoresWithDefaultValuesUsed
     */
    public function __construct(
        ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        UrlPersistInterface $urlPersist,
        ProductUrlPathGenerator $productUrlPathGenerator = null,
        GetStoresWithDefaultValuesUsed $getStoresWithDefaultValuesUsed = null
    ) {
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
        $this->productUrlPathGenerator = $productUrlPathGenerator ?: ObjectManager::getInstance()
            ->get(ProductUrlPathGenerator::class);
        $this->getStoresWithDefaultValuesUsed = $getStoresWithDefaultValuesUsed ?? ObjectManager::getInstance()
                ->get(GetStoresWithDefaultValuesUsed::class);
    }

    /**
     * Generate urls for UrlRewrite and save it in storage
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Product $product */
        $product = $observer->getEvent()->getProduct();

        if ($product->dataHasChangedFor('url_key')
            || $product->getIsChangedCategories()
            || $product->getIsChangedWebsites()
            || $product->dataHasChangedFor('visibility')
        ) {
            if ($product->isVisibleInSiteVisibility()) {
                $product->unsUrlPath();
                $product->setUrlPath($this->productUrlPathGenerator->getUrlPath($product));
                $this->urlPersist->replace($this->productUrlRewriteGenerator->generate($product));
            } else {
                $this->urlPersist->deleteByData(
                    [
                        UrlRewrite::ENTITY_ID => $product->getId(),
                        UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                        UrlRewrite::STORE_ID => $product->getStoreId() ?:
                            $this->getStoresWithDefaultValuesUsed->execute(
                                'visibility',
                                $product->getId(),
                                Product::ENTITY
                            )
                    ]
                );
            }
        }
    }
}
