<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model;

use Magento\Checkout\Model\CompositeConfigProvider;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CompositeConfigProviderTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $configProviderMock;

    /**
     * @var CompositeConfigProvider
     */
    protected $model;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->configProviderMock = $this->getMockForAbstractClass(ConfigProviderInterface::class);
        $this->model = $objectManager->getObject(
            CompositeConfigProvider::class,
            ['configProviders' => [$this->configProviderMock]]
        );
    }

    public function testGetConfig()
    {
        $config = ['key' => 'value'];
        $this->configProviderMock->expects($this->once())->method('getConfig')->willReturn($config);
        $this->assertEquals($config, $this->model->getConfig());
    }
}
