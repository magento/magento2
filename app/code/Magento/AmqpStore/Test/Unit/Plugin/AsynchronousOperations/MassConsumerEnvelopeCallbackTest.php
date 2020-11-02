<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AmqpStore\Test\Unit\Plugin\AsynchronousOperations;

use Magento\AmqpStore\Plugin\AsynchronousOperations\MassConsumerEnvelopeCallback;
use Magento\AsynchronousOperations\Model\MassConsumerEnvelopeCallback as SubjectMassConsumerEnvelopeCallback;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PhpAmqpLib\Wire\AMQPTable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MassConsumerEnvelopeCallbackTest extends TestCase
{
    /**
     * @var MassConsumerEnvelopeCallback
     */
    private $massConsumerEnvelopeCallbackPlugin;

    /**
     * @var EnvelopeFactory|MockObject
     */
    private $envelopeFactoryMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Psr\Log\LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var SubjectMassConsumerEnvelopeCallback|MockObject
     */
    private $subjectMassConsumerEnvelopeCallbackMock;

    /**
     * @var EnvelopeInterface|MockObject
     */
    private $messageMock;

    /**
     * @var Store|MockObject
     */
    private $storeMock;

    protected function setUp(): void
    {
        $this->subjectMassConsumerEnvelopeCallbackMock = $this->createMock(SubjectMassConsumerEnvelopeCallback::class);
        $this->storeMock = $this->createMock(Store::class);
        $this->messageMock = $this->getMockForAbstractClass(EnvelopeInterface::class);

        $this->envelopeFactoryMock = $this->createMock(EnvelopeFactory::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->loggerMock = $this->getMockForAbstractClass(\Psr\Log\LoggerInterface::class);

        $objectManager = new ObjectManager($this);
        $this->massConsumerEnvelopeCallbackPlugin = $objectManager->getObject(
            MassConsumerEnvelopeCallback::class,
            [
                'envelopeFactory' => $this->envelopeFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'logger' => $this->loggerMock,
            ]
        );
    }

    public function testAroundExecuteWhenApplicationHeadersDoesNotExist()
    {
        $this->messageMock->expects($this->once())
            ->method('getProperties')
            ->willReturn(null);

        $isProceedCalled = false;
        $proceed = function ($message) use (&$isProceedCalled) {
            $isProceedCalled = !!$message;
        };

        $this->massConsumerEnvelopeCallbackPlugin->aroundExecute(
            $this->subjectMassConsumerEnvelopeCallbackMock,
            $proceed,
            $this->messageMock
        );

        $this->assertTrue($isProceedCalled);
    }

    public function testAroundExecuteWhenCanNotGetCurrentStoreId()
    {
        $storeId = 333;
        $headers = ['store_id' => $storeId];

        $amqpProperties = ['application_headers' => $headers];
        $this->messageMock->expects($this->once())
            ->method('getProperties')
            ->willReturn($amqpProperties);

        $message = 'no_such_entity_exception';
        $this->storeManagerMock
            ->expects($this->once())
            ->method('getStore')
            ->willThrowException(new NoSuchEntityException(__($message)));

        $this->loggerMock
            ->expects($this->once())
            ->method('error')
            ->with("Can't set currentStoreId during processing queue. Message rejected. Error $message.");

        $queue = $this->getMockBuilder(QueueInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->subjectMassConsumerEnvelopeCallbackMock
            ->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);
        $queue
            ->expects($this->once())
            ->method('reject')
            ->with($this->messageMock, false, $message);

        $isProceedCalled = false;
        $proceed = function ($message) use (&$isProceedCalled) {
            $isProceedCalled = !!$message;
        };

        $this->massConsumerEnvelopeCallbackPlugin->aroundExecute(
            $this->subjectMassConsumerEnvelopeCallbackMock,
            $proceed,
            $this->messageMock
        );

        $this->assertFalse($isProceedCalled);
    }

    /**
     * @dataProvider provideApplicationHeadersForAroundExecuteSuccess
     *
     * @param array|AMQPTable $headers
     * @param int $storeId
     * @param int $currentStoreId
     */
    public function testAroundExecuteWhenSuccess($headers, int $storeId, int $currentStoreId)
    {
        $amqpProperties = ['application_headers' => $headers];
        $this->messageMock->expects($this->once())
            ->method('getProperties')
            ->willReturn($amqpProperties);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($currentStoreId);

        $this->storeManagerMock->expects($this->exactly(2))
            ->method('setCurrentStore')
            ->withConsecutive(
                [$storeId],
                [$currentStoreId]
            );

        $isProceedCalled = false;
        $proceed = function ($message) use (&$isProceedCalled) {
            $isProceedCalled = !!$message;
        };

        $this->massConsumerEnvelopeCallbackPlugin->aroundExecute(
            $this->subjectMassConsumerEnvelopeCallbackMock,
            $proceed,
            $this->messageMock
        );

        $this->assertTrue($isProceedCalled);
    }

    public function provideApplicationHeadersForAroundExecuteSuccess()
    {
        $storeId = 123;
        $currentStoreId = 99;

        return [
            [['store_id' => 123], $storeId, $currentStoreId],
            [new AMQPTable(['store_id' => 123]), $storeId, $currentStoreId],
        ];
    }
}
