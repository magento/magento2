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
namespace Magento\ConfigurableProduct\Helper\Product\Configuration;

class PluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Helper\Product\Configuration\Plugin
     */
    protected $plugin;

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

    /**
     * @var \Closure
     */
    protected $closureMock;

    protected function setUp()
    {
        $this->itemMock = $this->getMock('Magento\Catalog\Model\Product\Configuration\Item\ItemInterface');
        $this->productMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $this->typeInstanceMock = $this->getMock(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable',
            array('getSelectedAttributesInfo', '__wakeup'),
            array(),
            '',
            false
        );
        $this->itemMock->expects($this->once())->method('getProduct')->will($this->returnValue($this->productMock));
        $this->closureMock = function () {
            return array('options');
        };
        $this->subjectMock = $this->getMock(
            'Magento\Catalog\Helper\Product\Configuration',
            array(),
            array(),
            '',
            false
        );
        $this->plugin = new \Magento\ConfigurableProduct\Helper\Product\Configuration\Plugin();
    }

    public function testAroundGetOptionsWhenProductTypeIsConfigurable()
    {
        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeId'
        )->will(
            $this->returnValue(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
        );
        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeInstance'
        )->will(
            $this->returnValue($this->typeInstanceMock)
        );
        $this->typeInstanceMock->expects(
            $this->once()
        )->method(
            'getSelectedAttributesInfo'
        )->with(
            $this->productMock
        )->will(
            $this->returnValue(array('attributes'))
        );
        $this->assertEquals(
            array('attributes', 'options'),
            $this->plugin->aroundGetOptions($this->subjectMock, $this->closureMock, $this->itemMock)
        );
    }

    public function testAroundGetOptionsWhenProductTypeIsSimple()
    {
        $this->productMock->expects($this->once())->method('getTypeId')->will($this->returnValue('simple'));
        $this->productMock->expects($this->never())->method('getTypeInstance');
        $this->assertEquals(
            array('options'),
            $this->plugin->aroundGetOptions($this->subjectMock, $this->closureMock, $this->itemMock)
        );
    }
}
