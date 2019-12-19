<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Block\Cart;

use Magento\Checkout\Block\Cart\Shipping;
use Magento\Checkout\Model\CompositeConfigProvider;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\Serializer\JsonHexTag;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session as customerSession;
use Magento\Checkout\Model\Session as checkoutSession;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 *  Unit Test for Magento\Checkout\Block\Cart\Shipping
 */
class ShippingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Shipping
     */
    protected $block;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var customerSession|MockObject
     */
    protected $customerSessionMock;

    /**
     * @var checkoutSession|MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var CompositeConfigProvider|MockObject
     */
    protected $configProviderMock;

    /**
     * @var LayoutProcessorInterface|MockObject
     */
    protected $layoutProcessorMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var array
     */
    protected $layout;

    /**
     * @var Json|MockObject
     */
    private $serializerMock;

    /**
     * @var JsonHexTag|MockObject
     */
    private $jsonHexTagSerializerMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->customerSessionMock = $this->createMock(customerSession::class);
        $this->checkoutSessionMock = $this->createMock(checkoutSession::class);
        $this->configProviderMock = $this->createMock(CompositeConfigProvider::class);
        $this->layoutProcessorMock = $this->createMock(LayoutProcessorInterface::class);
        $this->serializerMock = $this->createMock(JsonHexTag::class);
        $this->jsonHexTagSerializerMock = $this->createMock(JsonHexTag::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->layout = [
            'components' => [
                'firstComponent' => ['param' => 'value'],
                'secondComponent' => ['param' => 'value'],
            ]
        ];

        $this->contextMock->expects($this->once())
            ->method('getStoreManager')
            ->willReturn($this->storeManagerMock);

        $this->block = new Shipping(
            $this->contextMock,
            $this->customerSessionMock,
            $this->checkoutSessionMock,
            $this->configProviderMock,
            [$this->layoutProcessorMock],
            ['jsLayout' => $this->layout],
            $this->serializerMock,
            $this->jsonHexTagSerializerMock
        );
    }

    /**
     * Test for getCheckoutConfig
     *
     * @return void
     */
    public function testGetCheckoutConfig(): void
    {
        $config = ['param' => 'value'];
        $this->configProviderMock->expects($this->once())
            ->method('getConfig')
            ->willReturn($config);

        $this->assertEquals($config, $this->block->getCheckoutConfig());
    }

    /**
     * Test for getJsLayout()
     *
     * @return void
     * @dataProvider getJsLayoutDataProvider
     */
    public function testGetJsLayout(array $layoutProcessed, string $jsonLayoutProcessed): void
    {
        $this->layoutProcessorMock->expects($this->once())
            ->method('process')
            ->with($this->layout)
            ->willReturn($layoutProcessed);

        $this->jsonHexTagSerializerMock->expects($this->once())
            ->method('serialize')
            ->willReturn($jsonLayoutProcessed);

        $this->assertEquals($jsonLayoutProcessed, $this->block->getJsLayout());
    }

    /**
     * Data for getJsLayout()
     *
     * @return array
     */
    public function getJsLayoutDataProvider(): array
    {
        $layoutProcessed = $this->layout;
        $layoutProcessed['components']['thirdComponent'] = ['param' => 'value'];
        return [
            [
                $layoutProcessed,
                '{"components":{"firstComponent":{"param":"value"},"secondComponent":{"param":"value"},"thirdComponent":{"param":"value"}}}'
            ]
        ];
    }

    /**
     * Test for getBaseUrl()
     *
     * @return void
     */
    public function testGetBaseUrl(): void
    {
        $baseUrl = 'baseUrl';
        $storeMock = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getBaseUrl']);
        $storeMock->expects($this->once())
            ->method('getBaseUrl')
            ->willReturn($baseUrl);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->assertEquals($baseUrl, $this->block->getBaseUrl());
    }

    /**
     * Test for getSerializedCheckoutConfig()
     *
     * @return void
     * @dataProvider jsonEncodeDataProvider
     */
    public function testGetSerializedCheckoutConfig(array $checkoutConfig, string $expectedJson): void
    {
        $this->configProviderMock->expects($this->once())
            ->method('getConfig')
            ->willReturn($checkoutConfig);

        $this->jsonHexTagSerializerMock->expects($this->once())
            ->method('serialize')
            ->willReturn($expectedJson);

        $this->assertEquals($expectedJson, $this->block->getSerializedCheckoutConfig());
    }

    /**
     * Data for getSerializedCheckoutConfig()
     *
     * @return array
     */
    public function jsonEncodeDataProvider(): array
    {
        return [
            [
                ['checkout', 'config'],
                '["checkout","config"]'
            ]
        ];
    }
}
