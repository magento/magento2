<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model\Cart;

use Magento\Checkout\Model\Cart\CheckoutSummaryConfigProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CheckoutSummaryConfigProviderTest extends TestCase
{
    /**
     * @var MockObject|UrlInterface
     */
    private $urlBuilderMock;

    /**
     * @var MockObject|ScopeConfigInterface
     */
    private $scopeConfigMock;

    /**
     * @var CheckoutSummaryConfigProvider
     */
    private $model;

    protected function setUp(): void
    {
        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMock();
        $this->model = new CheckoutSummaryConfigProvider($this->urlBuilderMock, $this->scopeConfigMock);
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
