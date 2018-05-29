<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\Indexer\Product;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\CatalogRule\Model\ResourceModel\Rule;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;

/**
 * @magentoDataFixtureBeforeTransaction Magento/CatalogRule/_files/attribute.php
 * @magentoDataFixtureBeforeTransaction Magento/CatalogRule/_files/product_with_attribute.php
 * @magentoDataFixtureBeforeTransaction Magento/CatalogRule/_files/rule_by_attribute.php
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class PriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Rule
     */
    private $resourceRule;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->resourceRule = Bootstrap::getObjectManager()->get(Rule::class);
    }

    /**
     * @return void
     */
    public function testPriceApplying() : void
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

        $this->assertEquals($simpleProduct->getMinimalPrice(), $confProduct->getMinimalPrice());
    }

    /**
     * @magentoAppArea frontend
     *
     * @return void
     */
    public function testSortByPrice() : void
    {
        $searchCriteria = Bootstrap::getObjectManager()->create(SearchCriteriaInterface::class);
        $sortOrder = Bootstrap::getObjectManager()->create(SortOrder::class);
        $sortOrder->setField('price')->setDirection(SortOrder::SORT_ASC);
        $searchCriteria->setSortOrders([$sortOrder]);
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $searchResults = $productRepository->getList($searchCriteria);
        $products = $searchResults->getItems();

        /** @var \Magento\Catalog\Model\Product $product1 */
        $product1 = array_values($products)[0];
        $product1->setPriceCalculation(false);
        $this->assertEquals('simple1', $product1->getSku());
        $rulePrice = $this->resourceRule->getRulePrice(new \DateTime(), 1, 1, $product1->getId());
        $this->assertEquals($rulePrice, $product1->getFinalPrice());
    }
}
