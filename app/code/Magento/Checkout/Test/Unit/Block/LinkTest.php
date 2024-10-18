<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Block;

use Magento\Checkout\Block\Link;
use Magento\Checkout\Helper\Data;
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
        $path = 'checkout';
        $url = 'http://example.com/';

        $urlBuilder = $this->getMockForAbstractClass(UrlInterface::class);
        $urlBuilder->expects($this->once())->method('getUrl')->with($path)->willReturn($url . $path);

        $context = $this->_objectManagerHelper->getObject(
            Context::class,
            ['urlBuilder' => $urlBuilder]
        );
        $link = $this->_objectManagerHelper->getObject(Link::class, ['context' => $context]);
        $this->assertEquals($url . $path, $link->getHref());
    }

    /**
     * @dataProvider toHtmlDataProvider
     */
    public function testToHtml($canOnepageCheckout, $isOutputEnabled)
    {
        $helper = $this->getMockBuilder(
            Data::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['canOnepageCheckout', 'isModuleOutputEnabled']
            )->getMock();

        $moduleManager = $this->getMockBuilder(
            Manager::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['isOutputEnabled']
            )->getMock();

        /** @var Link $block */
        $block = $this->_objectManagerHelper->getObject(
            Link::class,
            ['moduleManager' => $moduleManager, 'checkoutHelper' => $helper]
        );
        $helper->expects($this->any())->method('canOnepageCheckout')->willReturn($canOnepageCheckout);
        $moduleManager->expects(
            $this->any()
        )->method(
            'isOutputEnabled'
        )->with(
            'Magento_Checkout'
        )->willReturn(
            $isOutputEnabled
        );
        $this->assertEquals('', $block->toHtml());
    }

    /**
     * @return array
     */
    public static function toHtmlDataProvider()
    {
        return [[false, true], [true, false], [false, false]];
    }
}
