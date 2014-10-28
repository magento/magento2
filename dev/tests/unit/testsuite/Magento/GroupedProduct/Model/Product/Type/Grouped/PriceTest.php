<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\GroupedProduct\Model\Product\Type\Grouped;

class PriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\GroupedProduct\Model\Product\Type\Grouped\Price
     */
    protected $finalPriceModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    protected function setUp()
    {
        $this->productMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->finalPriceModel = $helper->getObject(
            'Magento\GroupedProduct\Model\Product\Type\Grouped\Price',
            array()
        );
    }

    /**
     * @covers Magento\GroupedProduct\Model\Product\Type\Grouped\Price::getFinalPrice
     */
    public function testGetFinalPriceIfQtyIsNullAndFinalPriceExist()
    {
        $finalPrice = 15;

        $this->productMock->expects(
            $this->any()
        )->method(
            'getCalculatedFinalPrice'
        )->will(
            $this->returnValue($finalPrice)
        );

        $this->productMock->expects($this->never())->method('hasCustomOptions');

        $this->assertEquals($finalPrice, $this->finalPriceModel->getFinalPrice(null, $this->productMock));
    }

    /**
     * @param array $associatedProducts
     * @param array $options
     * @param $expectedPriceCall
     * @param $expectedFinalPrice
     *
     * @dataProvider getFinalPriceDataProvider
     * @covers Magento\GroupedProduct\Model\Product\Type\Grouped\Price::getFinalPrice
     */
    public function testGetFinalPrice(
        array $associatedProducts,
        array $options,
        $expectedPriceCall,
        $expectedFinalPrice
    ) {
        $rawFinalPrice = 10;
        $rawPriceCheckStep = 10;

        $this->productMock->expects(
            $this->any()
        )->method(
            'getCalculatedFinalPrice'
        )->will(
            $this->returnValue($rawFinalPrice)
        );

        //mock for parent::getFinal price call
        $this->productMock->expects($this->any())->method('getPrice')->will($this->returnValue($rawFinalPrice));

        $this->productMock->expects(
            $this->at($rawPriceCheckStep)
        )->method(
            'setFinalPrice'
        )->with(
            $rawFinalPrice
        )->will(
            $this->returnValue($this->productMock)
        );

        $this->productMock->expects($this->at($expectedPriceCall))->method('setFinalPrice')->with($expectedFinalPrice);

        $this->productMock->expects(
            $this->any()
        )->method(
            'getData'
        )->with(
            'final_price'
        )->will(
            $this->returnValue($rawFinalPrice)
        );

        //test method
        $this->productMock->expects($this->once())->method('hasCustomOptions')->will($this->returnValue(true));

        $productTypeMock = $this->getMock(
            'Magento\GroupedProduct\Model\Product\Type\Grouped',
            array(),
            array(),
            '',
            false
        );

        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeInstance'
        )->will(
            $this->returnValue($productTypeMock)
        );

        $this->productMock->expects($this->any())->method('getStore')->will($this->returnValue('store1'));

        $productTypeMock->expects(
            $this->once()
        )->method(
            'setStoreFilter'
        )->with(
            'store1',
            $this->productMock
        )->will(
            $this->returnValue($productTypeMock)
        );

        $productTypeMock->expects(
            $this->once()
        )->method(
            'getAssociatedProducts'
        )->with(
            $this->productMock
        )->will(
            $this->returnValue($associatedProducts)
        );

        $this->productMock->expects($this->any())->method('getCustomOption')->will($this->returnValueMap($options));

        $this->assertEquals($rawFinalPrice, $this->finalPriceModel->getFinalPrice(1, $this->productMock));
    }

    /**
     * Data provider for testGetFinalPrice
     *
     * @return array
     */
    public function getFinalPriceDataProvider()
    {
        $optionMock = $this->getMock(
            'Magento\Catalog\Model\Product\Option',
            array('getValue', '__wakeup'),
            array(),
            '',
            false
        );
        /* quantity of options */
        $optionMock->expects($this->any())->method('getValue')->will($this->returnValue(5));

        return array(
            'custom_option_null' => array(
                'associatedProducts' => array(),
                'options' => array(array(), array()),
                'expectedPriceCall' => 14, /* product call number to check final price formed correctly */
                'expectedFinalPrice' => 10 /* 10(product price) + 2(options count) * 5(qty) * 5(option price) */
            ),
            'custom_option_exist' => array(
                'associatedProducts' => $this->generateAssociatedProducts(),
                'options' => array(
                    array('associated_product_1', false),
                    array('associated_product_2', $optionMock),
                    array('associated_product_3', $optionMock)
                ),
                'expectedPriceCall' => 17, /* product call number to check final price formed correctly */
                'expectedFinalPrice' => 35 /* 10(product price) + 2(options count) * 5(qty) * 5(option price) */
            )
        );
    }

    /**
     * Generate associated product for every custom option
     *
     * @return array
     */
    protected function generateAssociatedProducts()
    {
        $childProductMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            array('getId', 'getFinalPrice', '__wakeup'),
            array(),
            '',
            false
        );
        /* price for option taking into account quantity discounts */
        $childProductMock->expects($this->any())->method('getFinalPrice')->with(5)->will($this->returnValue(5));

        for ($i = 0; $i <= 2; $i++) {
            $childProduct = clone $childProductMock;
            $childProduct->expects($this->once())->method('getId')->will($this->returnValue($i));
            $associatedProducts[] = $childProduct;
        }

        return $associatedProducts;
    }
}
