<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Test\Unit\Model\Logger\Handler;

use Magento\Config\Setup\ConfigOptionsList;
use Magento\Developer\Model\Logger\Handler\Debug;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DebugTest extends TestCase
{
    /**
     * @var Debug
     */
    private $model;

    /**
     * @var DriverInterface|MockObject
     */
    private $filesystemMock;

    /**
     * @var State|MockObject
     */
    private $stateMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var FormatterInterface|MockObject
     */
    private $formatterMock;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var LogRecord
     */
    private $logRecord;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->filesystemMock = $this->getMockBuilder(DriverInterface::class)
            ->getMockForAbstractClass();
        $this->stateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();
        $this->formatterMock = $this->getMockBuilder(FormatterInterface::class)
            ->getMockForAbstractClass();
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();

        $this->formatterMock->expects($this->any())
            ->method('format')
            ->willReturn(null);

        $this->model = (new ObjectManager($this))->getObject(Debug::class, [
            'filesystem' => $this->filesystemMock,
            'state' => $this->stateMock,
            'scopeConfig' => $this->scopeConfigMock,
            'deploymentConfig' => $this->deploymentConfigMock
        ]);
        $this->model->setFormatter($this->formatterMock);

        $this->logRecord = new LogRecord(
            new \DateTimeImmutable(),
            'testChannel',
            Level::Debug,
            'testMessage'
        );
    }

    /**
     * @return void
     */
    public function testHandleEnabledInDeveloperMode()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->stateMock
            ->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_DEVELOPER);
        $this->scopeConfigMock
            ->expects($this->never())
            ->method('getValue');

        $this->assertTrue($this->model->isHandling($this->logRecord));
    }

    /**
     * @return void
     */
    public function testHandleEnabledInDefaultMode()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->stateMock
            ->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_DEFAULT);
        $this->scopeConfigMock
            ->expects($this->never())
            ->method('getValue');

        $this->assertTrue($this->model->isHandling($this->logRecord));
    }

    /**
     * @return void
     */
    public function testHandleDisabledByProduction()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->stateMock
            ->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_PRODUCTION);
        $this->scopeConfigMock
            ->expects($this->never())
            ->method('getValue');

        $this->assertFalse($this->model->isHandling($this->logRecord));
    }

    /**
     * @return void
     */
    public function testHandleDisabledByLevel()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(ConfigOptionsList::CONFIG_PATH_DEBUG_LOGGING)
            ->willReturn(false);

        $this->assertFalse($this->model->isHandling(
            new LogRecord(
                new \DateTimeImmutable(),
                'testChannel',
                Level::Error,
                'testMessage'
            )
        ));
    }

    /**
     * @return void
     */
    public function testDeploymentConfigIsNotAvailable()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(false);
        $this->stateMock
            ->expects($this->never())
            ->method('getMode');
        $this->scopeConfigMock
            ->expects($this->never())
            ->method('getValue');

        $this->assertTrue($this->model->isHandling($this->logRecord));
    }
}
