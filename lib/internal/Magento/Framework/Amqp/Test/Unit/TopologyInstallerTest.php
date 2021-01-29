<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp\Test\Unit;

use Magento\Framework\Amqp\TopologyInstaller;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\MessageQueue\Topology\ConfigInterface;
use PhpAmqpLib\Exception\AMQPLogicException;
use Psr\Log\LoggerInterface;

/**
 * Unit tests for @see \Magento\Framework\Amqp\TopologyInstaller
 */
class TopologyInstallerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Amqp\TopologyInstaller
     */
    private $topologyInstaller;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ConfigInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    private $topologyConfigMock;

    /**
     * @var LoggerInterface | \PHPUnit\Framework\MockObject\MockObject
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
