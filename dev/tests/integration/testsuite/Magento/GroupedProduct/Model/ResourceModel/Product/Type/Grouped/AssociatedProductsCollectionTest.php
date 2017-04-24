<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $productRepository->get('grouped-product');

        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\Registry::class)->register('current_product', $product);

        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\GroupedProduct\Model\ResourceModel\Product\Type\Grouped\AssociatedProductsCollection::class
        );

        $resultData = $collection->getColumnValues('sku');
        $this->assertNotEmpty($resultData);

        $expected = ['virtual-product', 'simple'];
        sort($expected);
        sort($resultData);
        $this->assertEquals($expected, $resultData);
    }
}
