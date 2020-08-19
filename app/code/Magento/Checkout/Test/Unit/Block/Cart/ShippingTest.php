<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Block\Cart;

use Magento\Checkout\Block\Cart\Shipping;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Checkout\Model\CompositeConfigProvider;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\Serializer\JsonHexTag;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 *  Unit Test for Magento\Checkout\Block\Cart\Shipping
 */
class ShippingTest extends TestCase
{

    /**
     * Stub Preinitialized Componets
     */
    private const STUB_PREINITIALIZED_COMPONENTS = [
        'components' => [
            'firstComponent' => ['param' => 'value']
        ]
    ];

    /**
     * Stub Base URL
     */
    private const STUB_BASE_URL = 'baseurl';

    /**
     * @var Shipping
     */
    protected $block;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var CustomerSession|MockObject
     */
    protected $customerSessionMock;

    /**
     * @var CheckoutSession|MockObject
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
    protected $storeManagerInterfaceMock;

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
        $this->customerSessionMock = $this->createMock(CustomerSession::class);
        $this->checkoutSessionMock = $this->createMock(CheckoutSession::class);
        $this->configProviderMock = $this->createMock(CompositeConfigProvider::class);
        $this->layoutProcessorMock = $this->getMockForAbstractClass(LayoutProcessorInterface::class);
        $this->serializerMock = $this->createMock(JsonHexTag::class);
        $this->jsonHexTagSerializerMock = $this->createMock(JsonHexTag::class);
        $this->storeManagerInterfaceMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->layout = self::STUB_PREINITIALIZED_COMPONENTS;

        $objectManager = new ObjectManager($this);
        $this->block = $objectManager->getObject(
            Shipping::class,
            [
                'configProvider' => $this->configProviderMock,
                'layoutProcessors' => [$this->layoutProcessorMock],
                'jsLayout' => $this->layout,
                'serializer' => $this->serializerMock,
                'jsonHexTagSerializer' => $this->jsonHexTagSerializerMock,
                'storeManager' => $this->storeManagerInterfaceMock
            ]
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
        $layoutProcessed['components']['secondComponent'] = ['param' => 'value'];
        return [
            [
                $layoutProcessed,
                '{"components":{"firstComponent":{"param":"value"},"secondComponent":{"param":"value"}}}'
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
        $baseUrl = self::STUB_BASE_URL;
        $storeMock = $this->createPartialMock(Store::class, ['getBaseUrl']);
        $storeMock->expects($this->once())
            ->method('getBaseUrl')
            ->willReturn($baseUrl);

        $this->storeManagerInterfaceMock->expects($this->once())
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
