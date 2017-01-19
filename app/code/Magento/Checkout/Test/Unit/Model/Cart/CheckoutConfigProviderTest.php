<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Unit\Model\Cart;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Checkout\Model\Cart\CheckoutConfigProvider;
use Magento\Store\Model\ScopeInterface;

class CheckoutConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\UrlInterface
     */
    private $urlBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfigMock;

    /**
     * @var \Magento\Checkout\Model\Cart\CheckoutConfigProvider
     */
    private $model;

    protected function setUp()
    {
        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)->getMock();
        $this->model = new CheckoutConfigProvider($this->urlBuilderMock, $this->scopeConfigMock);
    }

    public function testGetConfig()
    {
        $maxItemsCount = 10;
        $cartUrl = 'url/to/cart/page';
        $expectedResult = [
            'maxCartItemsToDisplay' => $maxItemsCount,
            'cartUrl' => $cartUrl
        ];

        $this->urlBuilderMock->expects($this->once())->method('getUrl')->with('checkout/cart')->willReturn($cartUrl);
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('checkout/options/max_items_display_count', ScopeInterface::SCOPE_STORE)
            ->willReturn($maxItemsCount);

        $this->assertEquals($expectedResult, $this->model->getConfig());
    }
}
