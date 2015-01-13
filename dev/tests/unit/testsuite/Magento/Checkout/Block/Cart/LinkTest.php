<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart;

class LinkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManagerHelper;

    protected function setUp()
    {
        $this->_objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    public function testGetUrl()
    {
        $path = 'checkout/cart';
        $url = 'http://example.com/';

        $urlBuilder = $this->getMockForAbstractClass('Magento\Framework\UrlInterface');
        $urlBuilder->expects($this->once())->method('getUrl')->with($path)->will($this->returnValue($url . $path));

        $helper = $this->getMockBuilder('Magento\Core\Helper\Data')->disableOriginalConstructor()->getMock();

        $context = $this->_objectManagerHelper->getObject(
            'Magento\Framework\View\Element\Template\Context',
            ['urlBuilder' => $urlBuilder]
        );
        $link = $this->_objectManagerHelper->getObject(
            'Magento\Checkout\Block\Cart\Link',
            ['coreData' => $helper, 'context' => $context]
        );
        $this->assertSame($url . $path, $link->getHref());
    }

    public function testToHtml()
    {
        $moduleManager = $this->getMockBuilder(
            'Magento\Framework\Module\Manager'
        )->disableOriginalConstructor()->setMethods(
            ['isOutputEnabled']
        )->getMock();
        $helper = $this->getMockBuilder('Magento\Checkout\Helper\Cart')->disableOriginalConstructor()->getMock();

        /** @var \Magento\Checkout\Block\Cart\Link $block */
        $block = $this->_objectManagerHelper->getObject(
            'Magento\Checkout\Block\Cart\Link',
            ['cartHelper' => $helper, 'moduleManager' => $moduleManager]
        );
        $moduleManager->expects(
            $this->any()
        )->method(
            'isOutputEnabled'
        )->with(
            'Magento_Checkout'
        )->will(
            $this->returnValue(true)
        );
        $this->assertSame('', $block->toHtml());
    }

    /**
     * @dataProvider getLabelDataProvider
     */
    public function testGetLabel($productCount, $label)
    {
        $helper = $this->getMockBuilder(
            'Magento\Checkout\Helper\Cart'
        )->disableOriginalConstructor()->setMethods(
            ['getSummaryCount']
        )->getMock();

        /** @var \Magento\Checkout\Block\Cart\Link $block */
        $block = $this->_objectManagerHelper->getObject(
            'Magento\Checkout\Block\Cart\Link',
            ['cartHelper' => $helper]
        );
        $helper->expects($this->any())->method('getSummaryCount')->will($this->returnValue($productCount));
        $this->assertSame($label, (string)$block->getLabel());
    }

    public function getLabelDataProvider()
    {
        return [[1, 'My Cart (1 item)'], [2, 'My Cart (2 items)'], [0, 'My Cart']];
    }
}
