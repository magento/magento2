<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Model\Mode;

use Magento\Csp\Api\Data\ModeConfiguredInterface;
use Magento\Csp\Model\Mode\ConfigManager;
use Magento\Csp\Model\Mode\Data\ModeConfiguredFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\State;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
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
     * @var Http|MockObject
     */
    private $requestMock;

    /**
     * @var ModeConfiguredFactory
     */
    private $modeConfiguredFactoryMock;

    /**
     * @var ModeConfiguredInterface
     */
    private $modeConfiguredInterfaceMock;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->storeMock = $this->createMock(Store::class);
        $this->stateMock = $this->createMock(State::class);
        $this->requestMock = $this->createMock(Http::class);
        $this->modeConfiguredFactoryMock = $this->createPartialMock(ModeConfiguredFactory::class, ['create']);
        $this->modeConfiguredInterfaceMock = $this->createMock(ModeConfiguredInterface::class);

        $this->model = $objectManager->getObject(
            ConfigManager::class,
            [
                'config' => $this->scopeConfigMock,
                'storeModel' => $this->storeMock,
                'state' => $this->stateMock,
                'request' => $this->requestMock,
                'modeConfiguredFactory' => $this->modeConfiguredFactoryMock
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
        $this->modeConfiguredFactoryMock->expects($this->once())
            ->method('create')
            ->with(['reportOnly' => true, 'reportUri' => 'testReportUri'])
            ->willReturn($this->modeConfiguredInterfaceMock);

        $result = $this->model->getConfigured();

        $this->assertInstanceOf(ModeConfiguredInterface::class, $result);
    }

    /**
     * Test storefront checkout page CSP config.
     *
     * @return void
     *
     */
    public function testCheckoutPageReportOnly(): void
    {
        $this->requestMock->expects($this->exactly(2))
            ->method('getFullActionName')
            ->willReturn('checkout_index_index');

        $this->stateMock->expects($this->once())
            ->method('getAreaCode')
            ->willReturn(Area::AREA_FRONTEND);

        $matcher = $this->exactly(2);
        $this->scopeConfigMock->expects($matcher)
            ->method('getValue')
            ->willReturnCallback(function () use ($matcher) {
                return match ($matcher->getInvocationCount()) {
                    1 => ['csp/mode/checkout_index_index/report_only', ScopeInterface::SCOPE_STORE, null],
                    2 => ['csp/mode/checkout_index_index/report_uri', ScopeInterface::SCOPE_STORE, null],
                };
            })
            ->willReturnOnConsecutiveCalls(true, 'testReportUri');

        $this->modeConfiguredFactoryMock->expects($this->once())
            ->method('create')
            ->with(['reportOnly' => true, 'reportUri' => 'testReportUri'])
            ->willReturn($this->modeConfiguredInterfaceMock);

        $result = $this->model->getConfigured();

        $this->assertInstanceOf(ModeConfiguredInterface::class, $result);
    }

    /**
     * Test non checkout page CSP config.
     *
     * @return void
     */
    public function testNonCheckoutPageReportOnly(): void
    {
        $this->requestMock->expects($this->exactly(2))
            ->method('getFullActionName')
            ->willReturn('dashboard_index_index');

        $this->stateMock->expects($this->once())
            ->method('getAreaCode')
            ->willReturn(Area::AREA_ADMINHTML);

        $matcher = $this->exactly(4);
        $this->scopeConfigMock->expects($matcher)
            ->method('getValue')
            ->willReturnCallback(function () use ($matcher) {
                return match ($matcher->getInvocationCount()) {
                    1 => ['csp/mode/dashboard_index_index/report_only', ScopeInterface::SCOPE_STORE, null],
                    2 => ['csp/mode/admin/report_only', ScopeInterface::SCOPE_STORE, null],
                    3 => ['csp/mode/dashboard_index_index/report_uri', ScopeInterface::SCOPE_STORE, null],
                    4 => ['csp/mode/admin/report_uri', ScopeInterface::SCOPE_STORE, null],
                };
            })
            ->willReturnOnConsecutiveCalls(null, true, null, 'testPageReportUri');

        $this->modeConfiguredFactoryMock->expects($this->once())
            ->method('create')
            ->with(['reportOnly' => true, 'reportUri' => 'testPageReportUri'])
            ->willReturn($this->modeConfiguredInterfaceMock);

        $result = $this->model->getConfigured();

        $this->assertInstanceOf(ModeConfiguredInterface::class, $result);
    }
}
