<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Captcha\Test\Unit\Model\Cart;

use Magento\Captcha\Model\Cart\ConfigPlugin;
use Magento\Captcha\Model\Checkout\ConfigProvider;
use Magento\Checkout\Block\Cart\Sidebar;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigPluginTest extends TestCase
{
    /**
     * @var ConfigPlugin
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $configProviderMock;

    protected function setUp(): void
    {
        $this->configProviderMock = $this->createMock(ConfigProvider::class);
        $this->model = new ConfigPlugin(
            $this->configProviderMock
        );
    }

    public function testAfterGetConfig()
    {
        $resultMock = [
            'result' => [
                'data' => 'resultDataMock'
            ]
        ];
        $configMock = [
            'config' => [
                'data' => 'configDataMock'
            ]
        ];
        $expectedResult = array_merge_recursive($resultMock, $configMock);
        $sidebarMock = $this->createMock(Sidebar::class);
        $this->configProviderMock->expects($this->once())->method('getConfig')->willReturn($configMock);

        $this->assertEquals($expectedResult, $this->model->afterGetConfig($sidebarMock, $resultMock));
    }
}
