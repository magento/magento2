<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Block;

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
        $path = 'checkout';
        $url = 'http://example.com/';

        $urlBuilder = $this->getMockForAbstractClass(\Magento\Framework\UrlInterface::class);
        $urlBuilder->expects($this->once())->method('getUrl')->with($path)->will($this->returnValue($url . $path));

        $context = $this->_objectManagerHelper->getObject(
            \Magento\Framework\View\Element\Template\Context::class,
            ['urlBuilder' => $urlBuilder]
        );
        $link = $this->_objectManagerHelper->getObject(\Magento\Checkout\Block\Link::class, ['context' => $context]);
        $this->assertEquals($url . $path, $link->getHref());
    }

    /**
     * @dataProvider toHtmlDataProvider
     */
    public function testToHtml($canOnepageCheckout, $isOutputEnabled)
    {
        $helper = $this->getMockBuilder(
            \Magento\Checkout\Helper\Data::class
        )->disableOriginalConstructor()->setMethods(
            ['canOnepageCheckout', 'isModuleOutputEnabled']
        )->getMock();

        $moduleManager = $this->getMockBuilder(
            \Magento\Framework\Module\Manager::class
        )->disableOriginalConstructor()->setMethods(
            ['isOutputEnabled']
        )->getMock();

        /** @var \Magento\Checkout\Block\Link $block */
        $block = $this->_objectManagerHelper->getObject(
            \Magento\Checkout\Block\Link::class,
            ['moduleManager' => $moduleManager, 'checkoutHelper' => $helper]
        );
        $helper->expects($this->any())->method('canOnepageCheckout')->will($this->returnValue($canOnepageCheckout));
        $moduleManager->expects(
            $this->any()
        )->method(
            'isOutputEnabled'
        )->with(
            'Magento_Checkout'
        )->will(
            $this->returnValue($isOutputEnabled)
        );
        $this->assertEquals('', $block->toHtml());
    }

    public function toHtmlDataProvider()
    {
        return [[false, true], [true, false], [false, false]];
    }
}
