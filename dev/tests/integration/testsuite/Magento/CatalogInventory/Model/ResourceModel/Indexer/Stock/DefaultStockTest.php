<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock;

/**
 * Class DefaultStockTest
 * @magentoAppArea adminhtml
 */
class DefaultStockTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\DefaultStock
     */
    private $indexer;

    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    private $stockConfiguration;

    protected function setUp()
    {
        $this->indexer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\DefaultStock::class
        );
        $this->stockConfiguration = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\CatalogInventory\Api\StockConfigurationInterface::class
        );
    }

    /**
     * @magentoDataFixture Magento/Store/_files/website.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testReindexEntity()
    {
        /** @var \Magento\Catalog\Model\ProductRepository $productRepository */
        $productRepository = $this->getObject(\Magento\Catalog\Model\ProductRepository::class);
        /** @var \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository */
        $websiteRepository = $this->getObject(
            \Magento\Store\Api\WebsiteRepositoryInterface::class
        );
        $product = $productRepository->get('simple');
        $testWebsite = $websiteRepository->get('test');
        $product->setWebsiteIds([1, $testWebsite->getId()])->save();

        /** @var \Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory $criteriaFactory */
        $criteriaFactory = $this->getObject(
            \Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory::class
        );
        /** @var \Magento\CatalogInventory\Api\StockStatusRepositoryInterface $stockStatusRepository */
        $stockStatusRepository = $this->getObject(
            \Magento\CatalogInventory\Api\StockStatusRepositoryInterface::class
        );
        $criteria = $criteriaFactory->create();
        $criteria->setProductsFilter([$product->getId()]);
        $criteria->addFilter('website', 'website_id', $this->stockConfiguration->getDefaultScopeId());
        $items = $stockStatusRepository->getList($criteria)->getItems();
        $this->assertEquals($product->getId(), $items[$product->getId()]->getProductId());
    }

    private function getObject($class)
    {
        return \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            $class
        );
    }
}
