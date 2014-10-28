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
namespace Magento\GroupedProduct\Helper\Product\Configuration\Plugin;

class GroupedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\GroupedProduct\Helper\Product\Configuration\Plugin\Grouped
     */
    protected $groupedConfigPlugin;

    /**
     * @var \Closure
     */
    protected $closureMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeInstanceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->groupedConfigPlugin = new Grouped();
        $this->itemMock = $this->getMock('Magento\Catalog\Model\Product\Configuration\Item\ItemInterface');
        $this->productMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $this->typeInstanceMock = $this->getMock(
            'Magento\GroupedProduct\Model\Product\Type\Grouped',
            array(),
            array(),
            '',
            false
        );

        $this->itemMock->expects($this->any())->method('getProduct')->will($this->returnValue($this->productMock));

        $this->productMock->expects(
            $this->any()
        )->method(
            'getTypeInstance'
        )->will(
            $this->returnValue($this->typeInstanceMock)
        );

        $this->subjectMock = $this->getMock(
            'Magento\Catalog\Helper\Product\Configuration',
            array(),
            array(),
            '',
            false
        );
    }

    /**
     * @covers Magento\GroupedProduct\Helper\Product\Configuration\Plugin\Grouped::aroundGetOptions
     */
    public function testAroundGetOptionsGroupedProductWithAssociated()
    {
        $associatedProductId = 'associatedId';
        $associatedProdName = 'associatedProductName';

        $associatedProdMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);

        $associatedProdMock->expects($this->once())->method('getId')->will($this->returnValue($associatedProductId));

        $associatedProdMock->expects($this->once())->method('getName')->will($this->returnValue($associatedProdName));

        $this->typeInstanceMock->expects(
            $this->once()
        )->method(
            'getAssociatedProducts'
        )->with(
            $this->productMock
        )->will(
            $this->returnValue(array($associatedProdMock))
        );

        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeId'
        )->will(
            $this->returnValue(\Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE)
        );

        $quantityItemMock = $this->getMock(
            'Magento\Catalog\Model\Product\Configuration\Item\ItemInterface',
            array('getValue', 'getProduct', 'getOptionByCode', 'getFileDownloadParams')
        );

        $quantityItemMock->expects($this->any())->method('getValue')->will($this->returnValue(1));

        $this->itemMock->expects(
            $this->once()
        )->method(
            'getOptionByCode'
        )->with(
            'associated_product_' . $associatedProductId
        )->will(
            $this->returnValue($quantityItemMock)
        );

        $returnValue = array(array('label' => 'productName', 'value' => 2));
        $this->closureMock = function () use ($returnValue) {
            return $returnValue;
        };

        $result = $this->groupedConfigPlugin->aroundGetOptions(
            $this->subjectMock,
            $this->closureMock,
            $this->itemMock
        );
        $expectedResult = array(
            array('label' => 'associatedProductName', 'value' => 1),
            array('label' => 'productName', 'value' => 2)
        );
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @covers Magento\GroupedProduct\Helper\Product\Configuration\Plugin\Grouped::aroundGetOptions
     */
    public function testAroundGetOptionsGroupedProductWithoutAssociated()
    {
        $this->typeInstanceMock->expects(
            $this->once()
        )->method(
            'getAssociatedProducts'
        )->with(
            $this->productMock
        )->will(
            $this->returnValue(false)
        );

        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeId'
        )->will(
            $this->returnValue(\Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE)
        );

        $chainCallResult = array(array('label' => 'label', 'value' => 'value'));

        $this->closureMock = function () use ($chainCallResult) {
            return $chainCallResult;
        };

        $result = $this->groupedConfigPlugin->aroundGetOptions(
            $this->subjectMock,
            $this->closureMock,
            $this->itemMock
        );
        $this->assertEquals($chainCallResult, $result);
    }

    /**
     * @covers Magento\GroupedProduct\Helper\Product\Configuration\Plugin\Grouped::aroundGetOptions
     */
    public function testAroundGetOptionsAnotherProductType()
    {
        $chainCallResult = array('result');

        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeId'
        )->will(
            $this->returnValue('other_product_type')
        );

        $this->closureMock = function () use ($chainCallResult) {
            return $chainCallResult;
        };
        $this->productMock->expects($this->never())->method('getTypeInstance');

        $result = $this->groupedConfigPlugin->aroundGetOptions(
            $this->subjectMock,
            $this->closureMock,
            $this->itemMock
        );
        $this->assertEquals($chainCallResult, $result);
    }
}
