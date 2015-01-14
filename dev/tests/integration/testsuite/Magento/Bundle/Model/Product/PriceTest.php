<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Product;

/**
 * @magentoDataFixture Magento/Bundle/_files/product_with_tier_pricing.php
 */
class PriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Bundle\Model\Product\Price
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Bundle\Model\Product\Price'
        );
    }

    public function testGetTierPrice()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load(3);
        // fixture

        // Note that this is really not the "tier price" but the "tier discount percentage"
        // so it is expected to be increasing instead of decreasing
        $this->assertEquals(8.0, $this->_model->getTierPrice(2, $product));
        $this->assertEquals(20.0, $this->_model->getTierPrice(3, $product));
        $this->assertEquals(20.0, $this->_model->getTierPrice(4, $product));
        $this->assertEquals(30.0, $this->_model->getTierPrice(5, $product));
    }
}
