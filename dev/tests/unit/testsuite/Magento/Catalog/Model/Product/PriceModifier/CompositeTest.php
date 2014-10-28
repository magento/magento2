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
namespace Magento\Catalog\Model\Product\PriceModifier;

class CompositeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\PriceModifier\Composite
     */
    protected $compositeModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceModifierMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManager');
        $this->productMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $this->priceModifierMock = $this->getMock('Magento\Catalog\Model\Product\PriceModifierInterface');
    }

    public function testModifyPriceIfModifierExists()
    {
        $this->compositeModel = new \Magento\Catalog\Model\Product\PriceModifier\Composite(
            $this->objectManagerMock,
            array('some_class_name')
        );
        $this->objectManagerMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'some_class_name'
        )->will(
            $this->returnValue($this->priceModifierMock)
        );
        $this->priceModifierMock->expects(
            $this->once()
        )->method(
            'modifyPrice'
        )->with(
            100,
            $this->productMock
        )->will(
            $this->returnValue(150)
        );
        $this->assertEquals(150, $this->compositeModel->modifyPrice(100, $this->productMock));
    }

    public function testModifyPriceIfModifierNotExists()
    {
        $this->compositeModel = new \Magento\Catalog\Model\Product\PriceModifier\Composite(
            $this->objectManagerMock,
            array()
        );
        $this->objectManagerMock->expects($this->never())->method('get');
        $this->assertEquals(100, $this->compositeModel->modifyPrice(100, $this->productMock));
    }
}
