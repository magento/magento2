<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Search\Adapter\Mysql\Filter\Builder\Term;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

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
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @param ProductUrlRewriteGenerator $productUrlRewriteGenerator
     * @param UrlPersistInterface $urlPersist
     * @param ProductUrlPathGenerator|null $productUrlPathGenerator
     * @param CollectionFactory|null $productCollectionFactory
     */
    public function __construct(
        ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        UrlPersistInterface $urlPersist,
        ProductUrlPathGenerator $productUrlPathGenerator = null,
        CollectionFactory $productCollectionFactory = null
    ) {
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
        $this->productUrlPathGenerator = $productUrlPathGenerator ?: ObjectManager::getInstance()
            ->get(ProductUrlPathGenerator::class);
        $this->productCollectionFactory = $productCollectionFactory ?: ObjectManager::getInstance()
            ->get(CollectionFactory::class);
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
                return;
            }
        }

        $this->validateUrlKey($product);
    }

    /**
     * @param Product $product
     * @throws UrlAlreadyExistsException
     */
    private function validateUrlKey(Product $product)
    {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addFieldToFilter(
            'url_key',
            [Term::CONDITION_OPERATOR_EQUALS => $product->getUrlKey()]
        );
        $productCollection->getSelect()->where('e.entity_id != ?', $product->getId());

        if ($productCollection->getItems()) {
            throw new UrlAlreadyExistsException();
        }
    }
}
