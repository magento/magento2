<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GiftMessage\Test\Unit\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\GiftMessage\Model\CompositeConfigProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CompositeConfigProviderTest extends TestCase
{
    /**
     * @var CompositeConfigProvider
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $configProviderMock;

    protected function setUp(): void
    {
        $this->configProviderMock = $this->getMockForAbstractClass(ConfigProviderInterface::class);
        $this->model = new CompositeConfigProvider([$this->configProviderMock]);
    }

    public function testGetConfig()
    {
        $configMock = ['configuration' => ['option_1' => 'enabled']];
        $this->configProviderMock->expects($this->once())->method('getConfig')->willReturn($configMock);

        $this->assertSame($configMock, $this->model->getConfig());
    }
}
