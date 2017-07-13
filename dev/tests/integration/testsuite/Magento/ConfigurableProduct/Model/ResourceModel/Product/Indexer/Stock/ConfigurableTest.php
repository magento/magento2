<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Stock;

class ConfigurableTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testReindexAll()
    {
        /** @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor $processor */
        $processor = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\CatalogInventory\Model\Indexer\Stock\Processor::class
        );

        $processor->reindexAll();

        /** @var \Magento\Catalog\Model\CategoryFactory $categoryFactory */
        $categoryFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Model\CategoryFactory::class
        );
        $category = $categoryFactory->create()->load(2);
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class
        );

        $productCollection->addUrlRewrite($category->getId());
        $productCollection->addAttributeToSelect('name');
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
            'Configurable OptionOption 1' => 1000,
            'Configurable OptionOption 2' => 1000,
            'Configurable Product' => 0
        ];

        /** @var $product \Magento\Catalog\Model\Product */
        foreach ($productCollection as $product) {
            $this->assertEquals($expectedResult[$product->getName()], $product->getQty());
        }
    }
}
