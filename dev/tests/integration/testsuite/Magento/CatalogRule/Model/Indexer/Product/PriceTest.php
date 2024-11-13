<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model\Indexer\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogRule\Model\ResourceModel\Rule;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->resourceRule = $this->objectManager->get(Rule::class);
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
