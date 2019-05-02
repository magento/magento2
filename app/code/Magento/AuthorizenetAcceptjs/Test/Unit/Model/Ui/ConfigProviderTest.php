<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Model\Ui;

use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\AuthorizenetAcceptjs\Model\Ui\ConfigProvider;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\CartInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\AuthorizenetAcceptjs\Model\Ui\ConfigProvider
 */
class ConfigProviderTest extends TestCase
{
    /**
     * @var CartInterface|MockObject
     */
    private $cart;

    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var ConfigProvider
     */
    private $provider;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->cart = $this->createMock(CartInterface::class);
        $this->config = $this->createMock(Config::class);
        $this->provider = $objectManagerHelper->getObject(
            ConfigProvider::class,
            [
                'config' => $this->config,
                'cart' => $this->cart,
            ]
        );
    }

    /**
     * @return void
     */
    public function testProviderRetrievesValues()
    {
        $this->cart->method('getStoreId')
            ->willReturn('123');

        $this->config->method('getClientKey')
            ->with('123')
            ->willReturn('foo');

        $this->config->method('getLoginId')
            ->with('123')
            ->willReturn('bar');

        $this->config->method('getEnvironment')
            ->with('123')
            ->willReturn('baz');

        $this->config->method('isCvvEnabled')
            ->with('123')
            ->willReturn(false);

        $expected = [
            'payment' => [
                Config::METHOD => [
                    'clientKey' => 'foo',
                    'apiLoginID' => 'bar',
                    'environment' => 'baz',
                    'useCvv' => false,
                ],
            ],
        ];

        $this->assertEquals($expected, $this->provider->getConfig());
    }
}
