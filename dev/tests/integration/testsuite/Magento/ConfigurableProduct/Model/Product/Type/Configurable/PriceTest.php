<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class PriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testGetFinalPrice()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load(1);
        // fixture

        /** @var $model \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Price */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable\Price'
        );

        // final price is the lowest price of configurable variations
        $this->assertEquals(10, $model->getFinalPrice(1, $product));
    }
}
