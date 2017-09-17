<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\Framework\Event\ObserverInterface;

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
     * @param ProductUrlRewriteGenerator $productUrlRewriteGenerator
     * @param UrlPersistInterface $urlPersist
     */
    public function __construct(
        ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        UrlPersistInterface $urlPersist
    ) {
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
    }

    /**
     * Generate urls for UrlRewrite and save it in storage
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
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
            $params = [            
                UrlRewrite::ENTITY_ID => $product->getId(),
                UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                UrlRewrite::REDIRECT_TYPE => 0
            ];
            // category and websites are global - but we can change it from store view
            // @todo getIsChangedCategories() works wrong. [works only after add category, after remove returns false]
            if ($product->getIsChangedCategories() || 
                $product->getIsChangedWebsites()) {
                $store = 0;   
            } else {
                if ($store = $product->getStoreId()) {
                    $params[UrlRewrite::STORE_ID] = $store;  // we are in specific store
                }
            }
            $this->urlPersist->deleteByData($params);
            if (!$store) {
                $stores = $product->getStoreIds();  // after global change, we should rebuild url for all visible stores              
            } else {
                $stores = [$store];
            }
            $productResource = $product->getResource();
            $siteVisibilities = $product->getVisibleInSiteVisibilities();
            $productId = $product->getId();
            foreach ($stores as $storeId) {     
                $visible = $productResource->getAttributeRawValue($productId,'visibility',$storeId);
                if (in_array($visible,$siteVisibilities)) {
                    $this->urlPersist->replace($this->productUrlRewriteGenerator->generate($product,null,$storeId));
                }
            }
        }
    }
}
