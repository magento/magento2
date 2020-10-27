<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AmqpStore\Test\Unit\Plugin\AsynchronousOperations;

use Magento\AmqpStore\Plugin\AsynchronousOperations\MassConsumerEnvelopeCallback;
use Magento\AsynchronousOperations\Model\MassConsumerEnvelopeCallback as SubjectMassConsumerEnvelopeCallback;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PhpAmqpLib\Wire\AMQPTable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

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
     * @var MockObject|LoggerInterface
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
        $this->messageMock = $this->getMockForAbstractClass(EnvelopeInterface::class);
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->envelopeFactoryMock = $this->createMock(EnvelopeFactory::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);

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
        // @SuppressWarnings(PHPMD.UnusedFormalParameter)
        $proceed = function ($attributeId) use (&$isProceedCalled) {
            $isProceedCalled = true;
        };

        $this->massConsumerEnvelopeCallbackPlugin->aroundExecute(
            $this->subjectMassConsumerEnvelopeCallbackMock,
            $proceed,
            $this->messageMock
        );

        $this->assertTrue($isProceedCalled);
    }

    public function testAroundExecuteWhenApplicationHeadersExist()
    {
        $storeId = 333;
        $currentStoreId = 99;
        $headers = ['store_id' => $storeId];

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
            ->method('setCurrentStore');

        $isProceedCalled = false;
        // @SuppressWarnings(PHPMD.UnusedFormalParameter)
        $proceed = function ($attributeId) use (&$isProceedCalled) {
            $isProceedCalled = true;
        };

        $this->massConsumerEnvelopeCallbackPlugin->aroundExecute(
            $this->subjectMassConsumerEnvelopeCallbackMock,
            $proceed,
            $this->messageMock
        );

        $this->assertTrue($isProceedCalled);
    }
}
