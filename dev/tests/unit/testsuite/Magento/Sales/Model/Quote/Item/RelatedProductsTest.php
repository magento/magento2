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
namespace Magento\Sales\Model\Quote\Item;

class RelatedProductsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Quote\Item\RelatedProducts
     */
    protected $model;

    /**
     * @var array
     */
    protected $relatedProductTypes;

    protected function setUp()
    {
        $this->relatedProductTypes = array('type1', 'type2', 'type3');
        $this->model = new \Magento\Sales\Model\Quote\Item\RelatedProducts($this->relatedProductTypes);
    }

    /**
     * @param string $optionValue
     * @param int|bool $productId
     * @param array $expectedResult
     *
     * @covers \Magento\Sales\Model\Quote\Item\RelatedProducts::getRelatedProductIds
     * @dataProvider getRelatedProductIdsDataProvider
     */
    public function testGetRelatedProductIds($optionValue, $productId, $expectedResult)
    {
        $quoteItemMock = $this->getMock('\Magento\Sales\Model\Quote\Item', array(), array(), '', false);
        $itemOptionMock = $this->getMock(
            '\Magento\Sales\Model\Quote\Item\Option',
            array('getValue', 'getProductId', '__wakeup'),
            array(),
            '',
            false
        );

        $quoteItemMock->expects(
            $this->once()
        )->method(
            'getOptionByCode'
        )->with(
            'product_type'
        )->will(
            $this->returnValue($itemOptionMock)
        );

        $itemOptionMock->expects($this->once())->method('getValue')->will($this->returnValue($optionValue));

        $itemOptionMock->expects($this->any())->method('getProductId')->will($this->returnValue($productId));

        $this->assertEquals($expectedResult, $this->model->getRelatedProductIds(array($quoteItemMock)));
    }

    /*
     * Data provider for testGetRelatedProductIds
     *
     * @return array
     */
    public function getRelatedProductIdsDataProvider()
    {
        return array(
            array('optionValue' => 'type1', 'productId' => 123, 'expectedResult' => array(123)),
            array('optionValue' => 'other_type', 'productId' => 123, 'expectedResult' => array()),
            array('optionValue' => 'type1', 'productId' => null, 'expectedResult' => array()),
            array('optionValue' => 'other_type', 'productId' => false, 'expectedResult' => array())
        );
    }

    /**
     * @covers \Magento\Sales\Model\Quote\Item\RelatedProducts::getRelatedProductIds
     */
    public function testGetRelatedProductIdsNoOptions()
    {
        $quoteItemMock = $this->getMock('\Magento\Sales\Model\Quote\Item', array(), array(), '', false);

        $quoteItemMock->expects(
            $this->once()
        )->method(
            'getOptionByCode'
        )->with(
            'product_type'
        )->will(
            $this->returnValue(new \stdClass())
        );

        $this->assertEquals(array(), $this->model->getRelatedProductIds(array($quoteItemMock)));
    }
}
