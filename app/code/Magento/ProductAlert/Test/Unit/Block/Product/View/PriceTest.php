<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Test\Unit\Block\Product\View;

/**
 * Test class for \Magento\ProductAlert\Block\Product\View\Price
 */
class PriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\ProductAlert\Helper\Data
     */
    protected $_helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\ProductAlert\Block\Product\View\Price
     */
    protected $_block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Layout
     */
    protected $_layout;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_helper = $this->getMock(
            \Magento\ProductAlert\Helper\Data::class,
            ['isPriceAlertAllowed', 'getSaveUrl'],
            [],
            '',
            false
        );
        $this->_product = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            ['getCanShowPrice', 'getId', '__wakeup'],
            [],
            '',
            false
        );
        $this->_product->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->_registry = $this->getMockBuilder(
            \Magento\Framework\Registry::class
        )->disableOriginalConstructor()->setMethods(
            ['registry']
        )->getMock();
        $this->_block = $objectManager->getObject(
            \Magento\ProductAlert\Block\Product\View\Price::class,
            ['helper' => $this->_helper, 'registry' => $this->_registry]
        );
        $this->_layout = $this->getMock(\Magento\Framework\View\Layout::class, [], [], '', false);
    }

    public function testSetTemplatePriceAlertAllowed()
    {
        $this->_helper->expects($this->once())->method('isPriceAlertAllowed')->will($this->returnValue(true));
        $this->_helper->expects(
            $this->once()
        )->method(
            'getSaveUrl'
        )->with(
            'price'
        )->will(
            $this->returnValue('http://url')
        );

        $this->_product->expects($this->once())->method('getCanShowPrice')->will($this->returnValue(true));

        $this->_registry->expects(
            $this->any()
        )->method(
            'registry'
        )->with(
            'current_product'
        )->will(
            $this->returnValue($this->_product)
        );

        $this->_block->setLayout($this->_layout);
        $this->_block->setTemplate('path/to/template.phtml');

        $this->assertEquals('path/to/template.phtml', $this->_block->getTemplate());
        $this->assertEquals('http://url', $this->_block->getSignupUrl());
    }

    /**
     * @param bool $priceAllowed
     * @param bool $showProductPrice
     *
     * @dataProvider setTemplatePriceAlertNotAllowedDataProvider
     */
    public function testSetTemplatePriceAlertNotAllowed($priceAllowed, $showProductPrice)
    {
        $this->_helper->expects($this->once())->method('isPriceAlertAllowed')->will($this->returnValue($priceAllowed));
        $this->_helper->expects($this->never())->method('getSaveUrl');

        $this->_product->expects($this->any())->method('getCanShowPrice')->will($this->returnValue($showProductPrice));

        $this->_registry->expects(
            $this->any()
        )->method(
            'registry'
        )->with(
            'current_product'
        )->will(
            $this->returnValue($this->_product)
        );

        $this->_block->setLayout($this->_layout);
        $this->_block->setTemplate('path/to/template.phtml');

        $this->assertEquals('', $this->_block->getTemplate());
        $this->assertNull($this->_block->getSignupUrl());
    }

    /**
     * @return array
     */
    public function setTemplatePriceAlertNotAllowedDataProvider()
    {
        return [
            'price alert is not allowed' => [false, true],
            'no product price' => [true, false],
            'price alert is not allowed and no product price' => [false, false]
        ];
    }

    public function testSetTemplateNoProduct()
    {
        $this->_helper->expects($this->once())->method('isPriceAlertAllowed')->will($this->returnValue(true));
        $this->_helper->expects($this->never())->method('getSaveUrl');

        $this->_registry->expects(
            $this->once()
        )->method(
            'registry'
        )->with(
            'current_product'
        )->will(
            $this->returnValue(null)
        );

        $this->_block->setLayout($this->_layout);
        $this->_block->setTemplate('path/to/template.phtml');

        $this->assertEquals('', $this->_block->getTemplate());
        $this->assertNull($this->_block->getSignupUrl());
    }
}
