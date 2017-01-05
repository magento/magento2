<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
class TopologyInstallerTest extends \PHPUnit_Framework_TestCase
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
     * @var ConfigInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $topologyConfigMock;

    /**
     * @var LoggerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * Initialize topology installer.
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->topologyConfigMock = $this->getMock(ConfigInterface::class);
        $this->loggerMock = $this->getMock(LoggerInterface::class);
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
