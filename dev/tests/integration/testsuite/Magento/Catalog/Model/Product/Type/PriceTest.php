<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Type;

use Magento\Catalog\Model\Product;

/**
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 */
class PriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Type\Price
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product\Type\Price'
        );
    }

    public function testGetPrice()
    {
        $this->assertEquals('test', $this->_model->getPrice(new \Magento\Framework\DataObject(['price' => 'test'])));
    }

    public function testGetFinalPrice()
    {
        $repository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\ProductRepository'
        );
        $product = $repository->get('simple');
        // fixture

        // regular & tier prices
        $this->assertEquals(10.0, $this->_model->getFinalPrice(1, $product));
        $this->assertEquals(8.0, $this->_model->getFinalPrice(2, $product));
        $this->assertEquals(5.0, $this->_model->getFinalPrice(5, $product));

        // with options
        $buyRequest = $this->prepareBuyRequest($product);
        $product->getTypeInstance()->prepareForCart($buyRequest, $product);

        //product price + options price(10+1+2+3+3)
        $this->assertEquals(19.0, $this->_model->getFinalPrice(1, $product));

        //product tier price + options price(5+1+2+3+3)
        $this->assertEquals(14.0, $this->_model->getFinalPrice(5, $product));
    }

    public function testGetFormatedPrice()
    {
        $repository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\ProductRepository'
        );
        $product = $repository->get('simple');
        // fixture
        $this->assertEquals('<span class="price">$10.00</span>', $this->_model->getFormatedPrice($product));
    }

    public function testCalculatePrice()
    {
        $this->assertEquals(10, $this->_model->calculatePrice(10, 8, '1970-12-12 23:59:59', '1971-01-01 01:01:01'));
        $this->assertEquals(8, $this->_model->calculatePrice(10, 8, '1970-12-12 23:59:59', '2034-01-01 01:01:01'));
    }

    public function testCalculateSpecialPrice()
    {
        $this->assertEquals(
            10,
            $this->_model->calculateSpecialPrice(10, 8, '1970-12-12 23:59:59', '1971-01-01 01:01:01')
        );
        $this->assertEquals(
            8,
            $this->_model->calculateSpecialPrice(10, 8, '1970-12-12 23:59:59', '2034-01-01 01:01:01')
        );
    }

    public function testIsTierPriceFixed()
    {
        $this->assertTrue($this->_model->isTierPriceFixed());
    }

    /**
     * Build buy request based on product custom options
     *
     * @param Product $product
     * @return \Magento\Framework\DataObject
     */
    private function prepareBuyRequest(Product $product)
    {
        $options = [];
        /** @var $option \Magento\Catalog\Model\Product\Option */
        foreach ($product->getOptions() as $option) {
            switch ($option->getGroupByType()) {
                case \Magento\Catalog\Model\Product\Option::OPTION_GROUP_DATE:
                    $value = ['year' => 2013, 'month' => 8, 'day' => 9, 'hour' => 13, 'minute' => 35];
                    break;
                case \Magento\Catalog\Model\Product\Option::OPTION_GROUP_SELECT:
                    $value = key($option->getValues());
                    break;
                default:
                    $value = 'test';
                    break;
            }
            $options[$option->getId()] = $value;
        }

        return new \Magento\Framework\DataObject(['qty' => 1, 'options' => $options]);
    }
}
