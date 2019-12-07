<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Block\Cart;

class LinkTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManagerHelper;

    protected function setUp()
    {
        $this->_objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    public function testGetUrl()
    {
        $path = 'checkout/cart';
        $url = 'http://example.com/';

        $urlBuilder = $this->getMockForAbstractClass(\Magento\Framework\UrlInterface::class);
        $urlBuilder->expects($this->once())->method('getUrl')->with($path)->will($this->returnValue($url . $path));

        $context = $this->_objectManagerHelper->getObject(
            \Magento\Framework\View\Element\Template\Context::class,
            ['urlBuilder' => $urlBuilder]
        );
        $link = $this->_objectManagerHelper->getObject(
            \Magento\Checkout\Block\Cart\Link::class,
            ['context' => $context]
        );
        $this->assertSame($url . $path, $link->getHref());
    }

    public function testToHtml()
    {
        $moduleManager = $this->getMockBuilder(
            \Magento\Framework\Module\Manager::class
        )->disableOriginalConstructor()->setMethods(
            ['isOutputEnabled']
        )->getMock();
        $helper = $this->getMockBuilder(\Magento\Checkout\Helper\Cart::class)->disableOriginalConstructor()->getMock();

        /** @var \Magento\Checkout\Block\Cart\Link $block */
        $block = $this->_objectManagerHelper->getObject(
            \Magento\Checkout\Block\Cart\Link::class,
            ['cartHelper' => $helper, 'moduleManager' => $moduleManager]
        );
        $moduleManager->expects(
            $this->any()
        )->method(
            'isOutputEnabled'
        )->with(
            'Magento_Checkout'
        )->will(
            $this->returnValue(false)
        );
        $this->assertSame('', $block->toHtml());
    }

    /**
     * @dataProvider getLabelDataProvider
     */
    public function testGetLabel($productCount, $label)
    {
        $helper = $this->getMockBuilder(
            \Magento\Checkout\Helper\Cart::class
        )->disableOriginalConstructor()->setMethods(
            ['getSummaryCount']
        )->getMock();

        /** @var \Magento\Checkout\Block\Cart\Link $block */
        $block = $this->_objectManagerHelper->getObject(
            \Magento\Checkout\Block\Cart\Link::class,
            ['cartHelper' => $helper]
        );
        $helper->expects($this->any())->method('getSummaryCount')->will($this->returnValue($productCount));
        $this->assertSame($label, (string)$block->getLabel());
    }

    /**
     * @return array
     */
    public function getLabelDataProvider()
    {
        return [[1, 'My Cart (1 item)'], [2, 'My Cart (2 items)'], [0, 'My Cart']];
    }
}
