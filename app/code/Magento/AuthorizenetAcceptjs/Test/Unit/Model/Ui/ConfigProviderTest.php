<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Model\Ui;

use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\AuthorizenetAcceptjs\Model\Ui\ConfigProvider;
use Magento\Quote\Api\Data\CartInterface;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    /**
     * @var CartInterface|MockObject|InvocationMocker
     */
    private $cart;

    /**
     * @var Config|MockObject|InvocationMocker
     */
    private $config;

    /**
     * @var ConfigProvider
     */
    private $provider;

    protected function setUp()
    {
        $this->cart = $this->createMock(CartInterface::class);
        $this->config = $this->createMock(Config::class);
        $this->provider = new ConfigProvider($this->config, $this->cart);
    }

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
                ]
            ]
        ];

        $this->assertEquals($expected, $this->provider->getConfig());
    }
}
