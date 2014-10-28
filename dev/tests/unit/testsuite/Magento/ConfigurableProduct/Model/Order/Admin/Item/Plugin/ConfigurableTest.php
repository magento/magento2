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
namespace Magento\ConfigurableProduct\Model\Order\Admin\Item\Plugin;

class ConfigurableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Model\Order\Admin\Item\Plugin\Configurable
     */
    protected $configurable;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->itemMock = $this->getMock(
            'Magento\Sales\Model\Order\Item',
            array('getProductType', 'getProductOptions', '__wakeup'),
            array(),
            '',
            false
        );
        $this->closureMock = function () {
            return 'Expected';
        };
        $this->productFactoryMock = $this->getMock('Magento\Catalog\Model\ProductFactory', array('create'));
        $this->productMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $this->subjectMock = $this->getMock('Magento\Sales\Model\Order\Admin\Item', array(), array(), '', false);
        $this->configurable = new \Magento\ConfigurableProduct\Model\Order\Admin\Item\Plugin\Configurable(
            $this->productFactoryMock
        );
    }

    public function testAroundGetNameIfProductIsConfigurable()
    {
        $this->itemMock->expects(
            $this->once()
        )->method(
            'getProductType'
        )->will(
            $this->returnValue(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
        );
        $this->itemMock->expects(
            $this->once()
        )->method(
            'getProductOptions'
        )->will(
            $this->returnValue(array('simple_name' => 'simpleName'))
        );
        $this->assertEquals(
            'simpleName',
            $this->configurable->aroundGetName($this->subjectMock, $this->closureMock, $this->itemMock)
        );
    }

    public function testAroundGetNameIfProductIsSimple()
    {
        $this->itemMock->expects($this->once())->method('getProductType')->will($this->returnValue('simple'));
        $this->itemMock->expects($this->never())->method('getProductOptions');
        $this->assertEquals(
            'Expected',
            $this->configurable->aroundGetName($this->subjectMock, $this->closureMock, $this->itemMock)
        );
    }

    public function testAroundGetSkuIfProductIsConfigurable()
    {
        $this->itemMock->expects(
            $this->once()
        )->method(
            'getProductType'
        )->will(
            $this->returnValue(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
        );
        $this->itemMock->expects(
            $this->once()
        )->method(
            'getProductOptions'
        )->will(
            $this->returnValue(array('simple_sku' => 'simpleName'))
        );
        $this->assertEquals(
            'simpleName',
            $this->configurable->aroundGetSku($this->subjectMock, $this->closureMock, $this->itemMock)
        );
    }

    public function testAroundGetSkuIfProductIsSimple()
    {
        $this->itemMock->expects($this->once())->method('getProductType')->will($this->returnValue('simple'));
        $this->itemMock->expects($this->never())->method('getProductOptions');
        $this->assertEquals(
            'Expected',
            $this->configurable->aroundGetSku($this->subjectMock, $this->closureMock, $this->itemMock)
        );
    }

    public function testAroundGetProductIdIfProductIsConfigurable()
    {
        $this->itemMock->expects(
            $this->once()
        )->method(
            'getProductType'
        )->will(
            $this->returnValue(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
        );
        $this->itemMock->expects(
            $this->once()
        )->method(
            'getProductOptions'
        )->will(
            $this->returnValue(array('simple_sku' => 'simpleName'))
        );
        $this->productFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->productMock)
        );
        $this->productMock->expects(
            $this->once()
        )->method(
            'getIdBySku'
        )->with(
            'simpleName'
        )->will(
            $this->returnValue('id')
        );
        $this->assertEquals(
            'id',
            $this->configurable->aroundGetProductId($this->subjectMock, $this->closureMock, $this->itemMock)
        );
    }

    public function testAroundGetProductIdIfProductIsSimple()
    {
        $this->itemMock->expects($this->once())->method('getProductType')->will($this->returnValue('simple'));
        $this->itemMock->expects($this->never())->method('getProductOptions');
        $this->assertEquals(
            'Expected',
            $this->configurable->aroundGetProductId($this->subjectMock, $this->closureMock, $this->itemMock)
        );
    }
}
