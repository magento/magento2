<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\View\Deployment;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\State;
use Magento\Framework\App\View\Deployment\Version;
use Magento\Framework\App\View\Deployment\Version\StorageInterface;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class VersionTest extends TestCase
{
    /**
     * @var Version
     */
    private $object;

    /**
     * @var State|MockObject
     */
    private $appStateMock;

    /**
     * @var StorageInterface|MockObject
     */
    private $versionStorageMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfigMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->appStateMock = $this->createMock(State::class);
        $this->versionStorageMock = $this->getMockForAbstractClass(StorageInterface::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->deploymentConfigMock = $this->createMock(DeploymentConfig::class);

        $this->object = new Version($this->appStateMock, $this->versionStorageMock, $this->deploymentConfigMock);
        $objectManager->setBackwardCompatibleProperty($this->object, 'logger', $this->loggerMock);
    }

    /**
     * @param string $appMode
     * @dataProvider getValueFromStorageDataProvider
     */
    public function testGetValueFromStorage($appMode)
    {
        $this->appStateMock
            ->expects($this->once())
            ->method('getMode')
            ->willReturn($appMode);
        $this->versionStorageMock->expects($this->once())
            ->method('load')
            ->willReturn('123');
        $this->versionStorageMock->expects($this->never())
            ->method('save');
        $this->assertEquals('123', $this->object->getValue());
        $this->object->getValue(); // Ensure caching in memory
    }

    /**
     * @return array
     */
    public static function getValueFromStorageDataProvider()
    {
        return [
            'default mode'      => [State::MODE_DEFAULT],
            'production mode'   => [State::MODE_PRODUCTION],
            'arbitrary mode'    => ['test'],
        ];
    }

    public function testGetValueInNonProductionMode()
    {
        $version = 123123123123;
        $this->versionStorageMock->expects($this->once())
            ->method('load')
            ->willReturn($version);

        $this->assertEquals($version, $this->object->getValue());
        $this->object->getValue();
    }

    public function testGetValueWithProductionModeAndException()
    {
        $this->expectException('UnexpectedValueException');
        $this->versionStorageMock->expects($this->once())
            ->method('load')
            ->willReturn(false);
        $this->appStateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_PRODUCTION);
        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(ConfigOptionsListConstants::CONFIG_PATH_SCD_ON_DEMAND_IN_PRODUCTION)
            ->willReturn(0);
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with('Can not load static content version.');

        $this->object->getValue();
    }

    public function testGetValueWithDefaultMode()
    {
        $this->versionStorageMock->expects($this->once())
            ->method('load')
            ->willReturn(false);
        $this->appStateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_DEFAULT);
        $this->versionStorageMock->expects($this->once())
            ->method('save');

        $this->assertNotNull($this->object->getValue());
    }

    public function testGetValueWithProductionModeAndEnabledSCDonDemand()
    {
        $this->versionStorageMock->expects($this->once())
            ->method('load')
            ->willReturn(false);
        $this->appStateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_PRODUCTION);
        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(ConfigOptionsListConstants::CONFIG_PATH_SCD_ON_DEMAND_IN_PRODUCTION)
            ->willReturn(1);
        $this->versionStorageMock->expects($this->once())
            ->method('save');

        $this->assertNotNull($this->object->getValue());
    }
}
