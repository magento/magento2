<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Plugin;

use Magento\Framework\App\State;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\NewRelicReporting\Model\Config as NewRelicConfig;
use Magento\NewRelicReporting\Model\NewRelicWrapper;
use Magento\NewRelicReporting\Plugin\StatePlugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test coverage for \Magento\NewRelicReporting\Plugin\StatePlugin
 */
class StatePluginTest extends TestCase
{
    /**
     * @var string
     */
    private const STUB_APP_NAME = 'app_name';

    /**
     * @var StatePlugin
     */
    private $statePlugin;

    /**
     * @var NewRelicConfig|MockObject
     */
    private $configMock;

    /**
     * @var NewRelicWrapper|MockObject
     */
    private $newRelicWrapperMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

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
        $this->configMock = $this->getMockBuilder(NewRelicConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->newRelicWrapperMock = $this->createMock(NewRelicWrapper::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->stateMock = $this->createMock(State::class);

        $this->statePlugin = $objectManager->getObject(
            StatePlugin::class,
            [
                'config' => $this->configMock,
                'newRelicWrapper' => $this->newRelicWrapperMock,
                'logger' => $this->loggerMock,
            ]
        );
    }

    /**
     * Tests setting the new relic app name
     */
    public function testSuccessfullySettingAppName(): void
    {
        $this->configMock->expects($this->once())->method('isSeparateApps')->willReturn(true);
        $this->configMock->expects($this->any())->method('getNewRelicAppName')
            ->willReturn(static::STUB_APP_NAME);
        $this->configMock->expects($this->once())->method('isNewRelicEnabled')->willReturn(true);
        $this->stateMock->expects($this->once())->method('getAreaCode')->willReturn('frontend');
        $this->newRelicWrapperMock->expects($this->once())->method('setAppName');

        $this->statePlugin->afterSetAreaCode($this->stateMock, static::STUB_APP_NAME);
    }

    /**
     * Tests not being able to set the New Relic app name
     *
     * @param bool $isSeparateApps
     * @param string $newRelicAppName
     * @param bool $enabled
     *
     * @dataProvider newRelicConfigDataProvider
     */
    public function testSuccessfullySettingAreaCode(bool $isSeparateApps, string $newRelicAppName, bool $enabled): void
    {
        $this->configMock->expects($this->any())->method('isSeparateApps')->willReturn($isSeparateApps);
        $this->configMock->expects($this->any())->method('getNewRelicAppName')->willReturn($newRelicAppName);
        $this->configMock->expects($this->any())->method('isNewRelicEnabled')->willReturn($enabled);
        $this->newRelicWrapperMock->expects($this->never())->method('setAppName');

        $this->statePlugin->afterSetAreaCode($this->stateMock, static::STUB_APP_NAME);
    }

    /**
     * New relic configuration data provider
     *
     * @return array
     */
    public function newRelicConfigDataProvider(): array
    {
        return [
            'Separate apps config is disabled' => [
                false,
                static::STUB_APP_NAME,
                true
            ],
            'Application name is not configured' => [
                true,
                '',
                true
            ],
            'New Relic is disabled' => [
                true,
                static::STUB_APP_NAME,
                false
            ]
        ];
    }
}
