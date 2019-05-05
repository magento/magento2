<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\Store;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\Store\Api\StoreWebsiteRelationInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Observer to assign the products to website
 */
class ProductToWebsiteChangeObserver implements ObserverInterface
{
    /**
     * @var ProductUrlRewriteGenerator
     */
    protected $productUrlRewriteGenerator;

    /**
     * @var UrlPersistInterface
     */
    protected $urlPersist;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var StoreWebsiteRelationInterface
     */
    private $storeWebsiteRelation;

    /**
     * @param ProductUrlRewriteGenerator $productUrlRewriteGenerator
     * @param UrlPersistInterface $urlPersist
     * @param ProductRepositoryInterface $productRepository
     * @param RequestInterface $request
     * @param StoreWebsiteRelationInterface $storeWebsiteRelation
     */
    public function __construct(
        ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        UrlPersistInterface $urlPersist,
        ProductRepositoryInterface $productRepository,
        RequestInterface $request,
        StoreWebsiteRelationInterface $storeWebsiteRelation = null
    ) {
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
        $this->productRepository = $productRepository;
        $this->request = $request;
        $this->storeWebsiteRelation = $storeWebsiteRelation ?:
            ObjectManager::getInstance()->get(StoreWebsiteRelationInterface::class);
    }

    /**
     * Generate urls for UrlRewrite and save it in storage
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        foreach ($observer->getEvent()->getProducts() as $productId) {
            $product = $this->productRepository->getById(
                $productId,
                false,
                $this->request->getParam('store_id', Store::DEFAULT_STORE_ID)
            );

            if (!empty($this->productUrlRewriteGenerator->generate($product))) {
                if ($this->request->getParam('remove_website_ids')) {
                    foreach ($this->request->getParam('remove_website_ids') as $webId) {
                        foreach ($this->storeWebsiteRelation->getStoreByWebsiteId($webId) as $storeId) {
                            $this->urlPersist->deleteByData([
                                UrlRewrite::ENTITY_ID => $product->getId(),
                                UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                                UrlRewrite::STORE_ID => $storeId
                            ]);
                        }
                    }
                }
                if ($product->getVisibility() != Visibility::VISIBILITY_NOT_VISIBLE) {
                    $this->urlPersist->replace($this->productUrlRewriteGenerator->generate($product));
                }
            }
        }
    }
}
