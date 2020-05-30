<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MessageQueue\Test\Unit\Model;

use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\ConsumerFactory;
use Magento\Framework\MessageQueue\ConsumerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MessageQueue\Model\ConsumerRunner;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConsumerRunnerTest extends TestCase
{
    const STUB_SLEEP_INTERVAL = 0;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ConsumerRunner
     */
    private $consumerRunner;

    /**
     * @var ConsumerFactory|MockObject
     */
    private $consumerFactoryMock;

    /**
     * @var MaintenanceMode|MockObject
     */
    private $maintenanceModeMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->consumerFactoryMock = $this->getMockBuilder(ConsumerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->maintenanceModeMock = $this->getMockBuilder(MaintenanceMode::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->consumerRunner = $this->objectManager->getObject(
            ConsumerRunner::class,
            [
                'consumerFactory' => $this->consumerFactoryMock,
                'maintenanceMode' => $this->maintenanceModeMock,
                'maintenanceSleepInterval' => self::STUB_SLEEP_INTERVAL
            ]
        );
    }

    /**
     * Ensure that consumer, with name equal to invoked magic method name, is run.
     *
     * @return void
     */
    public function testMagicMethod()
    {
        $isMaintenanceModeOn = false;
        /** @var ConsumerInterface|MockObject $consumerMock */
        $consumerMock = $this->getMockBuilder(ConsumerInterface::class)
            ->getMock();
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
        $this->expectException(LocalizedException::class);
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

        /** @var ConsumerInterface|MockObject $consumerMock */
        $consumerMock = $this->getMockBuilder(ConsumerInterface::class)
            ->getMock();
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
