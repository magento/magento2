<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * @magentoDbIsolation enabled
 * @magentoDataFixture Magento/ConfigurableProduct/_files/tax_rule.php
 * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
 */
class PriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testGetFinalPrice()
    {
        $this->validatePrice(10);
    }

    /**
     * @magentoConfigFixture current_store tax/display/type 1
     */
    public function testGetFinalPriceExcludingTax()
    {
        $this->validatePrice(10);
    }

    /**
     * @magentoConfigFixture current_store tax/display/type 2
     */
    public function testGetFinalPriceIncludingTax()
    {
        //lowest price of configurable variation + 10%
        $this->validatePrice(11);
    }

    /**
     * @magentoConfigFixture current_store tax/display/type 3
     */
    public function testGetFinalPriceIncludingExcludingTax()
    {
        //lowest price of configurable variation + 10%
        $this->validatePrice(11);
    }

    /**
     * Test
     *
     * @param $expectedPrice
     */
    protected function validatePrice($expectedPrice)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var $product \Magento\Catalog\Model\Product */
        $product = $objectManager->create('Magento\Catalog\Model\Product');
        $product->load(1);

        /** @var $model \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Price */
        $model = $objectManager->create(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable\Price'
        );

        // final price is the lowest price of configurable variations
        $this->assertEquals(round($expectedPrice, 2), round($model->getFinalPrice(1, $product), 2));
    }
}
