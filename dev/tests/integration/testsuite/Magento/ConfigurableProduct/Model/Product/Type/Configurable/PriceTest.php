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

        // without configurable options
        $this->assertEquals(100.0, $model->getFinalPrice(1, $product));

        // with configurable options
        $attributes = $product->getTypeInstance()->getConfigurableAttributes($product);
        foreach ($attributes as $attribute) {
            $prices = $attribute->getPrices();
            $product->addCustomOption(
                'attributes',
                serialize([$attribute->getProductAttribute()->getId() => $prices[0]['value_index']])
            );
            break;
        }
        $this->assertEquals(105.0, $model->getFinalPrice(1, $product));
    }
}
