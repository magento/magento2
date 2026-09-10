<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Amqp\Test\Unit;

use Magento\Framework\Amqp\TopologyInstaller;
use Magento\Framework\MessageQueue\Topology\ConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PhpAmqpLib\Exception\AMQPLogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit tests for @see \Magento\Framework\Amqp\TopologyInstaller
 */
class TopologyInstallerTest extends TestCase
{
    /**
     * @var TopologyInstaller
     */
    private $topologyInstaller;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ConfigInterface|MockObject
     */
    private $topologyConfigMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * Initialize topology installer.
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->topologyConfigMock = $this->createMock(ConfigInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->topologyInstaller = $this->objectManager->getObject(
            TopologyInstaller::class,
            ['topologyConfig' => $this->topologyConfigMock, 'logger' => $this->loggerMock]
        );
        parent::setUp();
    }

    /**
     * Make sure that topology creation errors are reported with the exception in the log context.
     */
    public function testInstallException()
    {
        $exception = new AMQPLogicException('Exception message');

        $this->topologyConfigMock
            ->expects($this->once())
            ->method('getQueues')
            ->willThrowException($exception);

        $this->loggerMock
            ->expects($this->once())
            ->method('error')
            ->with('AMQP topology installation failed', ['exception' => $exception]);

        $this->topologyInstaller->install();
    }
}
