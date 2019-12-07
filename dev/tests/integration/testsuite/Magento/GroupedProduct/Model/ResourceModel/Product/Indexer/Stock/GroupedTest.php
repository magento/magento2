<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Model\ResourceModel\Product\Indexer\Stock;

class GroupedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor
     */
    protected $processor;

    protected function setUp()
    {
        $this->processor = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\CatalogInventory\Model\Indexer\Stock\Processor::class
        );
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/GroupedProduct/_files/product_grouped.php
     */
    public function testReindexAll()
    {
        $this->processor->reindexAll();

        /** @var \Magento\Catalog\Model\CategoryFactory $categoryFactory */
        $categoryFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Model\CategoryFactory::class
        );
        $category = $categoryFactory->create()->load(2);
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class
        );

        $productCollection->addAttributeToSelect('name');
        $productCollection->addUrlRewrite($category->getId());
        $productCollection->joinField(
            'qty',
            'cataloginventory_stock_status',
            'qty',
            'product_id=entity_id',
            '{{table}}.stock_id=1',
            'left'
        );

        $this->assertCount(3, $productCollection);

        $expectedResult = [
            'Simple Product' => 22,
            'Virtual Product' => 10,
            'Grouped Product' => 0
        ];

        /** @var $product \Magento\Catalog\Model\Product */
        foreach ($productCollection as $product) {
            $this->assertEquals($expectedResult[$product->getName()], $product->getQty());
        }
    }
}
