<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Test\Unit\Model\Cart;

class ConfigPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Captcha\Model\Cart\ConfigPlugin
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $configProviderMock;

    protected function setUp(): void
    {
        $this->configProviderMock = $this->createMock(\Magento\Captcha\Model\Checkout\ConfigProvider::class);
        $this->model = new \Magento\Captcha\Model\Cart\ConfigPlugin(
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
        $sidebarMock = $this->createMock(\Magento\Checkout\Block\Cart\Sidebar::class);
        $this->configProviderMock->expects($this->once())->method('getConfig')->willReturn($configMock);

        $this->assertEquals($expectedResult, $this->model->afterGetConfig($sidebarMock, $resultMock));
    }
}
