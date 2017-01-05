<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
class ConsumerRunnerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    private $objectManager;

    /**
     * @var ConsumerRunner
     */
    private $consumerRunner;

    /**
     * @var \Magento\Framework\MessageQueue\ConsumerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $consumerFactoryMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->consumerFactoryMock = $this->getMockBuilder(\Magento\Framework\MessageQueue\ConsumerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->consumerRunner = $this->objectManager->getObject(
            \Magento\MessageQueue\Model\ConsumerRunner::class,
            ['consumerFactory' => $this->consumerFactoryMock]
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
        /** @var ConsumerInterface|\PHPUnit_Framework_MockObject_MockObject $consumerMock */
        $consumerMock = $this->getMockBuilder(\Magento\Framework\MessageQueue\ConsumerInterface::class)->getMock();
        $consumerMock->expects($this->once())->method('process');
        $consumerName = 'someConsumerName';
        $this->consumerFactoryMock
            ->expects($this->once())
            ->method('get')
            ->with($consumerName)
            ->willReturn($consumerMock);

        $this->consumerRunner->$consumerName();
    }

    /**
     * Ensure that exception will be thrown if requested magic method does not correspond to any declared consumer.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage "nonDeclaredConsumer" callback method specified in crontab.xml must
     * @return void
     */
    public function testMagicMethodNoRelatedConsumer()
    {
        $consumerName = 'nonDeclaredConsumer';
        $this->consumerFactoryMock
            ->expects($this->once())
            ->method('get')
            ->with($consumerName)
            ->willThrowException(new LocalizedException(new Phrase("Some exception")));

        $this->consumerRunner->$consumerName();
    }
}
