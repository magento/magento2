<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Developer\Test\Unit\Model\Logger\Handler;

use Magento\Config\Setup\ConfigOptionsList;
use Magento\Developer\Model\Logger\Handler\Syslog;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Logger\Monolog;
use Monolog\Handler\AbstractHandler;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;

/**
 * @inheritdoc
 */
class SyslogTest extends TestCase
{
    /**
     * @var Syslog
     */
    private $model;

    /**
     * @var ScopeConfigInterface|Mock
     */
    private $scopeConfigMock;

    /**
     * @var DeploymentConfig|Mock
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
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->deploymentConfigMock = $this->createMock(DeploymentConfig::class);
        $this->logRecord = new LogRecord(
            new \DateTimeImmutable(),
            'testChannel',
            Level::Debug,
            'testMessage'
        );

        $this->model = new Syslog(
            $this->deploymentConfigMock,
            'Magento'
        );
    }

    /**
     * @return void
     */
    public function testIsHandling(): void
    {
        $this->scopeConfigMock
            ->expects($this->never())
            ->method('getValue');
        $this->deploymentConfigMock
            ->expects($this->once())
            ->method('isDbAvailable')
            ->willReturn(true);
        $this->deploymentConfigMock
            ->expects($this->once())
            ->method('get')
            ->with(ConfigOptionsList::CONFIG_PATH_SYSLOG_LOGGING)
            ->willReturn(1);

        $this->assertTrue(
            $this->model->isHandling($this->logRecord)
        );
    }

    /**
     * @return void
     */
    public function testIsHandlingNotInstalled(): void
    {
        $this->scopeConfigMock
            ->expects($this->never())
            ->method('getValue');
        $this->deploymentConfigMock
            ->expects($this->once())
            ->method('isDbAvailable')
            ->willReturn(false);

        $this->assertFalse(
            $this->model->isHandling($this->logRecord)
        );
    }

    /**
     * @return void
     */
    public function testIsHandlingDisabled(): void
    {
        $this->scopeConfigMock
            ->expects($this->never())
            ->method('getValue');
        $this->deploymentConfigMock
            ->expects($this->once())
            ->method('isDbAvailable')
            ->willReturn(true);
        $this->deploymentConfigMock
            ->expects($this->once())
            ->method('get')
            ->with(ConfigOptionsList::CONFIG_PATH_SYSLOG_LOGGING)
            ->willReturn(0);

        $this->assertFalse(
            $this->model->isHandling($this->logRecord)
        );
    }
}
