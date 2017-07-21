<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @var \Magento\CatalogRule\Model\ResourceModel\Rule
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
        $this->indexerBuilder = Bootstrap::getObjectManager()->get(
            \Magento\CatalogRule\Model\Indexer\IndexBuilder::class
        );
        $this->resourceRule = Bootstrap::getObjectManager()->get(\Magento\CatalogRule\Model\ResourceModel\Rule::class);
        $this->product = Bootstrap::getObjectManager()->get(\Magento\Catalog\Model\Product::class);
    }

    protected function tearDown()
    {
        /** @var \Magento\Framework\Registry $registry */
        $registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\Registry::class);

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class
        );
        $productCollection->delete();

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);

        parent::tearDown();
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
        $product = $this->product->loadByAttribute('sku', 'simple');
        $product->load($product->getId());
        $product->setData('test_attribute', 'test_attribute_value')->save();

        $this->indexerBuilder->reindexById($product->getId());

        $this->assertEquals(9.8, $this->resourceRule->getRulePrice(new \DateTime(), 1, 1, $product->getId()));
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

        $this->indexerBuilder->reindexByIds(
            [
                $this->product->getId(),
                $this->productSecond->getId(),
                $this->productThird->getId(),
            ]
        );

        $this->assertEquals(9.8, $this->resourceRule->getRulePrice(new \DateTime(), 1, 1, $this->product->getId()));
        $this->assertEquals(
            9.8,
            $this->resourceRule->getRulePrice(new \DateTime(), 1, 1, $this->productSecond->getId())
        );
        $this->assertFalse($this->resourceRule->getRulePrice(new \DateTime(), 1, 1, $this->productThird->getId()));
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixtureBeforeTransaction Magento/CatalogRule/_files/attribute.php
     * @magentoDataFixtureBeforeTransaction Magento/CatalogRule/_files/rule_by_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testReindexFull()
    {
        $this->prepareProducts();

        $this->indexerBuilder->reindexFull();

        $rulePrice = $this->resourceRule->getRulePrice(new \DateTime(), 1, 1, $this->product->getId());
        $this->assertEquals(9.8, $rulePrice);
        $rulePrice = $this->resourceRule->getRulePrice(new \DateTime(), 1, 1, $this->productSecond->getId());
        $this->assertEquals(9.8, $rulePrice);
        $this->assertFalse($this->resourceRule->getRulePrice(new \DateTime(), 1, 1, $this->productThird->getId()));
    }

    protected function prepareProducts()
    {
        $product = $this->product->loadByAttribute('sku', 'simple');
        $product->load($product->getId());
        $this->product = $product;

        $this->product->setStoreId(0)->setData('test_attribute', 'test_attribute_value')->save();
        $this->productSecond = clone $this->product;
        $this->productSecond->setId(null)->setUrlKey('product-second')->save();
        $this->productThird = clone $this->product;
        $this->productThird->setId(null)
            ->setUrlKey('product-third')
            ->setData('test_attribute', 'NO_test_attribute_value')
            ->save();
    }
}
