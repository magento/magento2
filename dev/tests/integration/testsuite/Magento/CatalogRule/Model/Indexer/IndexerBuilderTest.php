<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model\Indexer;

use Magento\TestFramework\Helper\Bootstrap;

class IndexerBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Indexer\IndexBuilder
     */
    protected $indexerBuilder;

    /**
     * @var \Magento\CatalogRule\Model\Resource\Rule
     */
    protected $resourceRule;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productSecond;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productThird;

    protected function setUp()
    {
        $this->indexerBuilder = Bootstrap::getObjectManager()->get('Magento\CatalogRule\Model\Indexer\IndexBuilder');
        $this->resourceRule = Bootstrap::getObjectManager()->get('Magento\CatalogRule\Model\Resource\Rule');
        $this->product = Bootstrap::getObjectManager()->get('Magento\Catalog\Model\Product');
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/CatalogRule/_files/attribute.php
     * @magentoDataFixture Magento/CatalogRule/_files/rule_by_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testReindexById()
    {
        $this->product->load(1)->setData('test_attribute', 'test_attribute_value')->save();

        $this->indexerBuilder->reindexById(1);

        $this->assertEquals(9.8, $this->resourceRule->getRulePrice(true, 1, 1, 1));
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/CatalogRule/_files/attribute.php
     * @magentoDataFixture Magento/CatalogRule/_files/rule_by_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testReindexByIds()
    {
        $this->prepareProducts();

        $this->indexerBuilder->reindexByIds([
            $this->product->getId(),
            $this->productSecond->getId(),
            $this->productThird->getId(),
        ]);

        $this->assertEquals(9.8, $this->resourceRule->getRulePrice(true, 1, 1, 1));
        $this->assertEquals(9.8, $this->resourceRule->getRulePrice(true, 1, 1, $this->productSecond->getId()));
        $this->assertFalse($this->resourceRule->getRulePrice(true, 1, 1, $this->productThird->getId()));
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/CatalogRule/_files/attribute.php
     * @magentoDataFixture Magento/CatalogRule/_files/rule_by_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testReindexFull()
    {
        $this->prepareProducts();

        $this->indexerBuilder->reindexFull();

        $this->assertEquals(9.8, $this->resourceRule->getRulePrice(true, 1, 1, 1));
        $this->assertEquals(9.8, $this->resourceRule->getRulePrice(true, 1, 1, $this->productSecond->getId()));
        $this->assertFalse($this->resourceRule->getRulePrice(true, 1, 1, $this->productThird->getId()));
    }

    protected function prepareProducts()
    {
        $this->product->load(1)->setData('test_attribute', 'test_attribute_value')->save();
        $this->productSecond = clone $this->product;
        $this->productSecond->setId(null)->save();
        $this->productThird = clone $this->product;
        $this->productThird->setId(null)->setData('test_attribute', 'NO_test_attribute_value')->save();
    }
}
