<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Model\ResourceModel\Product\Type\Grouped;

class AssociatedProductsCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/GroupedProduct/_files/product_grouped.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     */
    public function testGetColumnValues()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load(9);
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\Registry')->register('current_product', $product);

        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\GroupedProduct\Model\ResourceModel\Product\Type\Grouped\AssociatedProductsCollection'
        );

        $this->assertEquals(['simple-1', 'virtual-product'], $collection->getColumnValues('sku'));
    }
}
