<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Type;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 */
class PriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Type\Price
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product\Type\Price::class
        );
    }

    public function testGetPrice()
    {
        $this->assertSame(9.0, $this->_model->getPrice(new \Magento\Framework\DataObject(['price' => '9.0'])));

        $repository = Bootstrap::getObjectManager()->create(ProductRepository::class);
        $product = $repository->get('simple');
        $this->assertSame(10.0, $this->_model->getPrice($product));
    }

    public function testGetBasePrice()
    {
        $this->assertSame(9.0, $this->_model->getPrice(new \Magento\Framework\DataObject(['price' => '9.0'])));

        $repository = Bootstrap::getObjectManager()->create(ProductRepository::class);
        $product = $repository->get('simple');
        $this->assertSame(10.0, $this->_model->getBasePrice($product));
    }

    public function testGetFinalPrice()
    {
        $repository = Bootstrap::getObjectManager()->create(ProductRepository::class);
        $product = $repository->get('simple');
        // fixture

        // regular & tier prices
        $this->assertSame(10.0, $this->_model->getFinalPrice(1, $product));
        $this->assertSame(8.0, $this->_model->getFinalPrice(2, $product));
        $this->assertSame(5.0, $this->_model->getFinalPrice(5, $product));

        // with options
        $buyRequest = $this->prepareBuyRequest($product);
        $product->getTypeInstance()->prepareForCart($buyRequest, $product);

        //product price + options price(10+1+2+3+3)
        $this->assertSame(19.0, $this->_model->getFinalPrice(1, $product));

        //product tier price + options price(5+1+2+3+3)
        $this->assertSame(14.0, $this->_model->getFinalPrice(5, $product));
    }

    public function testGetFormatedPrice()
    {
        $repository = Bootstrap::getObjectManager()->create(
            ProductRepository::class
        );
        $product = $repository->get('simple');
        // fixture
        $this->assertEquals('<span class="price">$10.00</span>', $this->_model->getFormatedPrice($product));
    }

    public function testCalculatePrice()
    {
        $this->assertSame(10, $this->_model->calculatePrice(10, 8, '1970-12-12 23:59:59', '1971-01-01 01:01:01'));
        $this->assertSame(8, $this->_model->calculatePrice(10, 8, '1970-12-12 23:59:59', '2034-01-01 01:01:01'));
    }

    public function testCalculateSpecialPrice()
    {
        $this->assertSame(
            10,
            $this->_model->calculateSpecialPrice(10, 8, '1970-12-12 23:59:59', '1971-01-01 01:01:01')
        );
        $this->assertSame(
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
                case \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_GROUP_DATE:
                    $value = ['year' => 2013, 'month' => 8, 'day' => 9, 'hour' => 13, 'minute' => 35];
                    break;
                case \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_GROUP_SELECT:
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
