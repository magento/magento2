<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Model\Mode;

use Magento\Csp\Model\Mode\ConfigManager;
use Magento\Csp\Model\Mode\Data\ModeConfigured;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Test for \Magento\Csp\Model\Mode\ConfigManager
 */
class ConfigManagerTest extends TestCase
{
    /**
     * @var ConfigManager
     */
    private $model;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Store|MockObject
     */
    private $storeMock;

    /**
     * @var State|MockObject
     */
    private $stateMock;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->storeMock = $this->createMock(Store::class);
        $this->stateMock = $this->createMock(State::class);

        $this->model = $objectManager->getObject(
            ConfigManager::class,
            [
                'config' => $this->scopeConfigMock,
                'storeModel' => $this->storeMock,
                'state' => $this->stateMock
            ]
        );
    }

    /**
     * Test throwing an exception for non storefront or admin areas
     *
     * @return void
     */
    public function testThrownExceptionForCrontabArea()
    {
        $this->stateMock->expects($this->any())
            ->method('getAreaCode')
            ->willReturn(Area::AREA_CRONTAB);

        $this->expectExceptionMessage('CSP can only be configured for storefront or admin area');
        $this->expectException(RuntimeException::class);

        $this->model->getConfigured();
    }

    /**
     * Test returning the configured CSP for admin area
     *
     * @return void
     */
    public function testConfiguredCSPForAdminArea()
    {
        $this->stateMock->expects($this->any())
            ->method('getAreaCode')
            ->willReturn(Area::AREA_ADMINHTML);
        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->willReturn(true);
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturn('testReportUri');
        $result = $this->model->getConfigured();

        $this->assertInstanceOf(ModeConfigured::class, $result);
    }
}
