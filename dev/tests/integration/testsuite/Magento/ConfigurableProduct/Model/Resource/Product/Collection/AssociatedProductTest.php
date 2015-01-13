<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Resource\Product\Collection;

class AssociatedProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_associated.php
     */
    public function testPrepareSelect()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load(1);
        // fixture
        $product->setId(10);
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\Registry')->register('current_product', $product);
        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\ConfigurableProduct\Model\Resource\Product\Collection\AssociatedProduct'
        );
        $collectionProduct = $collection->getFirstItem();
        $this->assertEquals($product->getName(), $collectionProduct->getName());
        $this->assertEquals($product->getSku(), $collectionProduct->getSku());
        $this->assertEquals($product->getPrice(), $collectionProduct->getPrice());
        $this->assertEquals($product->getWeight(), $collectionProduct->getWeight());
        $this->assertEquals($product->getTypeId(), $collectionProduct->getTypeId());
        $this->assertEquals($product->getAttributeSetId(), $collectionProduct->getAttributeSetId());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_associated.php
     */
    public function testPrepareSelectForSameProduct()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load(1);
        // fixture
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\Registry')->register('current_product', $product);
        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\ConfigurableProduct\Model\Resource\Product\Collection\AssociatedProduct'
        );
        $this->assertEmpty($collection->count());
    }
}
