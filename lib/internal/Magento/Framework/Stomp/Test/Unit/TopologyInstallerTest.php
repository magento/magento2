<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp\Test\Unit;

use Magento\Framework\MessageQueue\Topology\ConfigInterface;
use Magento\Framework\Stomp\TopologyInstaller;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Stomp\Exception\StompException;

/**
 * Unit tests for @see \Magento\Framework\Stomp\TopologyInstaller
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
            ->willThrowException(new StompException($exceptionMessage));

        $this->loggerMock
            ->expects($this->once())
            ->method('error')
            ->with($this->stringContains("STOMP topology installation failed: {$exceptionMessage}"));

        $this->topologyInstaller->install();
    }
}
