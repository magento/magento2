<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model\Indexer\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\CatalogRule\Model\ResourceModel\Rule;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

class PriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var Rule
     */
    private $resourceRule;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var IndexBuilder
     */
    private $indexerBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->resourceRule = $this->objectManager->get(Rule::class);
        $this->websiteRepository = $this->objectManager->get(WebsiteRepositoryInterface::class);
        $this->productRepository = $this->objectManager->create(ProductRepository::class);
        $this->indexerBuilder = $this->objectManager->get(IndexBuilder::class);
    }

    /**
     * @magentoDataFixtureBeforeTransaction Magento/CatalogRule/_files/configurable_product.php
     * @magentoDataFixtureBeforeTransaction Magento/CatalogRule/_files/rule_by_attribute.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testPriceApplying()
    {
        $customerGroupId = 1;
        $websiteId = 1;

        $simpleProductId = 1;
        $collection = Bootstrap::getObjectManager()->create(Collection::class);
        $collection->addIdFilter($simpleProductId);
        $collection->addPriceData($customerGroupId, $websiteId);
        $collection->load();
        /** @var \Magento\Catalog\Model\Product $simpleProduct */
        $simpleProduct = $collection->getFirstItem();
        $simpleProduct->setPriceCalculation(false);
        $rulePrice = $this->resourceRule->getRulePrice(new \DateTime(), $websiteId, $customerGroupId, $simpleProductId);
        $this->assertEquals($rulePrice, $simpleProduct->getFinalPrice());

        $confProductId = 666;
        $collection = Bootstrap::getObjectManager()->create(Collection::class);
        $collection->addIdFilter($confProductId);
        $collection->addPriceData($customerGroupId, $websiteId);
        $collection->load();
        /** @var \Magento\Catalog\Model\Product $confProduct */
        $confProduct = $collection->getFirstItem();
        $this->assertEquals($simpleProduct->getFinalPrice(), $confProduct->getMinimalPrice());
    }

    /**
     * @magentoDataFixtureBeforeTransaction Magento/CatalogRule/_files/simple_product_with_catalog_rule_50_percent_off.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @return void
     */
    public function testPriceForSecondStore():void
    {
        $websiteId = $this->websiteRepository->get('test')->getId();
        $simpleProduct = $this->productRepository->get('simple');
        $simpleProduct->setPriceCalculation(true);
        $this->assertEquals('simple', $simpleProduct->getSku());
        $this->assertFalse(
            $this->resourceRule->getRulePrice(new \DateTime(), $websiteId, 1, $simpleProduct->getId())
        );
        $this->indexerBuilder->reindexById($simpleProduct->getId());
        $this->assertEquals(
            $this->resourceRule->getRulePrice(new \DateTime(), $websiteId, 1, $simpleProduct->getId()),
            25
        );
    }

    /**
     * @magentoDataFixtureBeforeTransaction Magento/CatalogRule/_files/simple_products.php
     * @magentoDataFixtureBeforeTransaction Magento/CatalogRule/_files/rule_by_attribute.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testSortByPrice()
    {
        $searchCriteria = Bootstrap::getObjectManager()->create(SearchCriteriaInterface::class);
        $sortOrder = Bootstrap::getObjectManager()->create(SortOrder::class);
        $sortOrder->setField('price')->setDirection(SortOrder::SORT_ASC);
        $searchCriteria->setSortOrders([$sortOrder]);
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $searchResults = $productRepository->getList($searchCriteria);
        /** @var \Magento\Catalog\Model\Product[] $products */
        $products = array_values($searchResults->getItems());

        $product1 = $products[0];
        $product1->setPriceCalculation(false);
        $this->assertEquals('simple1', $product1->getSku());
        $rulePrice = $this->resourceRule->getRulePrice(new \DateTime(), 1, 1, $product1->getId());
        $this->assertEquals($rulePrice, $product1->getFinalPrice());
    }
}
