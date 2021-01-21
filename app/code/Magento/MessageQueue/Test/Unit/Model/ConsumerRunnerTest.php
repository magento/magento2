<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MessageQueue\Test\Unit\Model;

use Magento\Framework\MessageQueue\ConsumerInterface;
use Magento\MessageQueue\Model\ConsumerRunner;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Unit tests for consumer runner
 */
class ConsumerRunnerTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    private $objectManager;

    /**
     * @var ConsumerRunner
     */
    private $consumerRunner;

    /**
     * @var \Magento\Framework\MessageQueue\ConsumerFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $consumerFactoryMock;

    /**
     * @var \Magento\Framework\App\MaintenanceMode|\PHPUnit\Framework\MockObject\MockObject
     */
    private $maintenanceModeMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->consumerFactoryMock = $this->getMockBuilder(\Magento\Framework\MessageQueue\ConsumerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->maintenanceModeMock = $this->getMockBuilder(\Magento\Framework\App\MaintenanceMode::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->consumerRunner = $this->objectManager->getObject(
            \Magento\MessageQueue\Model\ConsumerRunner::class,
            [
                'consumerFactory' => $this->consumerFactoryMock,
                'maintenanceMode' => $this->maintenanceModeMock
            ]
        );
        parent::setUp();
    }

    /**
     * Ensure that consumer, with name equal to invoked magic method name, is run.
     *
     * @return void
     */
    public function testMagicMethod()
    {
        $isMaintenanceModeOn = false;
        /** @var ConsumerInterface|\PHPUnit\Framework\MockObject\MockObject $consumerMock */
        $consumerMock = $this->getMockBuilder(\Magento\Framework\MessageQueue\ConsumerInterface::class)->getMock();
        $consumerMock->expects($this->once())->method('process');
        $consumerName = 'someConsumerName';
        $this->consumerFactoryMock
            ->expects($this->once())
            ->method('get')
            ->with($consumerName)
            ->willReturn($consumerMock);
        $this->maintenanceModeMock->expects($this->once())->method('isOn')->willReturn($isMaintenanceModeOn);

        $this->consumerRunner->$consumerName();
    }

    /**
     * Ensure that exception will be thrown if requested magic method does not correspond to any declared consumer.
     *
     * @return void
     */
    public function testMagicMethodNoRelatedConsumer()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('"nonDeclaredConsumer" callback method specified in crontab.xml must');

        $consumerName = 'nonDeclaredConsumer';
        $this->consumerFactoryMock
            ->expects($this->once())
            ->method('get')
            ->with($consumerName)
            ->willThrowException(new LocalizedException(new Phrase("Some exception")));

        $this->consumerRunner->$consumerName();
    }

    /**
     * Ensure that process method will not be invoked if maintenance mode isOn returns true
     *
     * @return void
     */
    public function testMagicMethodMaintenanceModeIsOn()
    {
        $isMaintenanceModeOn = true;
        /** @var ConsumerInterface|\PHPUnit\Framework\MockObject\MockObject $consumerMock */
        $consumerMock = $this->getMockBuilder(\Magento\Framework\MessageQueue\ConsumerInterface::class)->getMock();
        $consumerMock->expects($this->never())->method('process');
        $consumerName = 'someConsumerName';
        $this->consumerFactoryMock
            ->expects($this->once())
            ->method('get')
            ->with($consumerName)
            ->willReturn($consumerMock);
        $this->maintenanceModeMock->expects($this->once())->method('isOn')->willReturn($isMaintenanceModeOn);

        $this->consumerRunner->$consumerName();
    }
}
