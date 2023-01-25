<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        $this->topologyConfigMock = $this->getMockForAbstractClass(ConfigInterface::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->topologyInstaller = $this->objectManager->getObject(
            TopologyInstaller::class,
            ['topologyConfig' => $this->topologyConfigMock, 'logger' => $this->loggerMock]
        );
        parent::setUp();
    }

    /**
     * Make sure that topology creation errors in log contain actual error message.
     */
    public function testInstallException()
    {
        $exceptionMessage = "Exception message";

        $this->topologyConfigMock
            ->expects($this->once())
            ->method('getQueues')
            ->willThrowException(new AMQPLogicException($exceptionMessage));

        $this->loggerMock
            ->expects($this->once())
            ->method('error')
            ->with($this->stringContains("AMQP topology installation failed: {$exceptionMessage}"));

        $this->topologyInstaller->install();
    }
}
