<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Block\Cart;

use Magento\Checkout\Block\Cart\Link;
use Magento\Checkout\Helper\Cart;
use Magento\Framework\Math\Random;
use Magento\Framework\Module\Manager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use PHPUnit\Framework\TestCase;

class LinkTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $_objectManagerHelper;

    protected function setUp(): void
    {
        $this->_objectManagerHelper = new ObjectManager($this);

        $objects = [
            [
                SecureHtmlRenderer::class,
                $this->createMock(SecureHtmlRenderer::class)
            ],
            [
                Random::class,
                $this->createMock(Random::class)
            ]
        ];
        $this->_objectManagerHelper->prepareObjectManager($objects);
    }

    public function testGetUrl()
    {
        $path = 'checkout/cart';
        $url = 'http://example.com/';

        $urlBuilder = $this->getMockForAbstractClass(UrlInterface::class);
        $urlBuilder->expects($this->once())->method('getUrl')->with($path)->willReturn($url . $path);

        $context = $this->_objectManagerHelper->getObject(
            Context::class,
            ['urlBuilder' => $urlBuilder]
        );
        $link = $this->_objectManagerHelper->getObject(
            Link::class,
            ['context' => $context]
        );
        $this->assertSame($url . $path, $link->getHref());
    }

    public function testToHtml()
    {
        $moduleManager = $this->getMockBuilder(
            Manager::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['isOutputEnabled']
            )->getMock();
        $helper = $this->getMockBuilder(Cart::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Link $block */
        $block = $this->_objectManagerHelper->getObject(
            Link::class,
            ['cartHelper' => $helper, 'moduleManager' => $moduleManager]
        );
        $moduleManager->expects(
            $this->any()
        )->method(
            'isOutputEnabled'
        )->with(
            'Magento_Checkout'
        )->willReturn(
            false
        );
        $this->assertSame('', $block->toHtml());
    }

    /**
     * @dataProvider getLabelDataProvider
     */
    public function testGetLabel($productCount, $label)
    {
        $helper = $this->getMockBuilder(
            Cart::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['getSummaryCount']
            )->getMock();

        /** @var Link $block */
        $block = $this->_objectManagerHelper->getObject(
            Link::class,
            ['cartHelper' => $helper]
        );
        $helper->expects($this->any())->method('getSummaryCount')->willReturn($productCount);
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
