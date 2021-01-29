<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Test\Unit\Model;

class CompositeConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\GiftMessage\Model\CompositeConfigProvider
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $configProviderMock;

    protected function setUp(): void
    {
        $this->configProviderMock = $this->createMock(\Magento\Checkout\Model\ConfigProviderInterface::class);
        $this->model = new \Magento\GiftMessage\Model\CompositeConfigProvider([$this->configProviderMock]);
    }

    public function testGetConfig()
    {
        $configMock = ['configuration' => ['option_1' => 'enabled']];
        $this->configProviderMock->expects($this->once())->method('getConfig')->willReturn($configMock);

        $this->assertSame($configMock, $this->model->getConfig());
    }
}
