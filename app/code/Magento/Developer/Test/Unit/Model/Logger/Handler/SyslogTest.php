<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Developer\Test\Unit\Model\Logger\Handler;

use Magento\Developer\Model\Logger\Handler\Syslog;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Logger\Monolog;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as Mock;

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

    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->deploymentConfigMock = $this->createMock(DeploymentConfig::class);

        $this->model = new Syslog(
            $this->scopeConfigMock,
            $this->deploymentConfigMock,
            'Magento'
        );
    }

    public function testIsHandling(): void
    {
        $record = [
            'level' => Monolog::DEBUG,
        ];

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Syslog::CONFIG_PATH)
            ->willReturn('1');
        $this->deploymentConfigMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);

        $this->assertTrue(
            $this->model->isHandling($record)
        );
    }

    public function testIsHandlingNotInstalled(): void
    {
        $record = [
            'level' => Monolog::DEBUG,
        ];

        $this->scopeConfigMock->expects($this->never())
            ->method('getValue');
        $this->deploymentConfigMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(false);

        $this->assertFalse(
            $this->model->isHandling($record)
        );
    }

    public function testIsHandlingDisabled(): void
    {
        $record = [
            'level' => Monolog::DEBUG,
        ];

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Syslog::CONFIG_PATH)
            ->willReturn('0');
        $this->deploymentConfigMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);

        $this->assertFalse(
            $this->model->isHandling($record)
        );
    }
}
