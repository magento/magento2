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
namespace Magento\GroupedProduct\Block\Product\View\Type;

class GroupedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\GroupedProduct\Block\Product\View\Type\Grouped
     */
    protected $groupedView;

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
    protected $configuredValueMock;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $methodsProduct = array(
            'getId',
            'setQty',
            'getTypeInstance',
            'getPreconfiguredValues',
            'getTypeId',
            '__wakeup'
        );
        $this->productMock = $this->getMock('Magento\Catalog\Model\Product', $methodsProduct, array(), '', false);
        $this->typeInstanceMock = $this->getMock(
            'Magento\GroupedProduct\Model\Product\Type\Grouped',
            array(),
            array(),
            '',
            false
        );
        $this->productMock->expects(
            $this->any()
        )->method(
            'getTypeInstance'
        )->will(
            $this->returnValue($this->typeInstanceMock)
        );
        $this->configuredValueMock = $this->getMock(
            'Magento\Framework\Object',
            array('getSuperGroup'),
            array(),
            '',
            false
        );
        $layout = $this->getMock('Magento\Framework\View\LayoutInterface');
        $this->groupedView = $helper->getObject(
            'Magento\GroupedProduct\Block\Product\View\Type\Grouped',
            array(
                'data' => array('product' => $this->productMock),
                'layout' => $layout
            )
        );
    }

    public function testGetAssociatedProducts()
    {

        $this->typeInstanceMock->expects(
            $this->once()
        )->method(
            'getAssociatedProducts'
        )->with(
            $this->productMock
        )->will(
            $this->returnValue('expected')
        );

        $this->assertEquals('expected', $this->groupedView->getAssociatedProducts());
    }

    /**
     * @param string $id
     * @dataProvider setPreconfiguredValueDataProvider
     */
    public function testSetPreconfiguredValue($id)
    {
        $configValue = array('id_one' => 2);
        $associatedProduct = array('key' => $this->productMock);
        $this->configuredValueMock->expects(
            $this->once()
        )->method(
            'getSuperGroup'
        )->will(
            $this->returnValue($configValue)
        );
        $this->productMock->expects(
            $this->once()
        )->method(
            'getPreconfiguredValues'
        )->will(
            $this->returnValue($this->configuredValueMock)
        );

        $this->typeInstanceMock->expects(
            $this->once()
        )->method(
            'getAssociatedProducts'
        )->with(
            $this->productMock
        )->will(
            $this->returnValue($associatedProduct)
        );


        $this->productMock->expects($this->any())->method('getId')->will($this->returnValue($id));
        $this->productMock->expects($this->any())->method('setQty')->with(2);
        $this->groupedView->setPreconfiguredValue();
    }

    public function setPreconfiguredValueDataProvider()
    {
        return array('item_id_exist_in_config' => array('id_one'), 'item_id_not_exist_in_config' => array('id_two'));
    }

    public function testSetPreconfiguredValueIfSuperGroupNotExist()
    {
        $this->productMock->expects(
            $this->once()
        )->method(
            'getPreconfiguredValues'
        )->will(
            $this->returnValue($this->configuredValueMock)
        );
        $this->configuredValueMock->expects($this->once())->method('getSuperGroup')->will($this->returnValue(false));
        $this->typeInstanceMock->expects($this->never())->method('getAssociatedProducts');
        $this->groupedView->setPreconfiguredValue();
    }

}
