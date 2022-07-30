<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AmqpStore\Test\Unit\Plugin\Framework\Amqp\Bulk;

use Magento\AmqpStore\Plugin\Framework\Amqp\Bulk\Exchange;
use Magento\Framework\Amqp\Bulk\Exchange as SubjectExchange;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PhpAmqpLib\Exception\AMQPInvalidArgumentException;
use PhpAmqpLib\Wire\AMQPTable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExchangeTest extends TestCase
{
    /**
     * @var Exchange
     */
    private $exchangePlugin;

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
     * @var SubjectExchange|MockObject
     */
    private $subjectExchange;

    /**
     * @var Store|MockObject
     */
    private $storeMock;

    protected function setUp(): void
    {
        $this->subjectExchange = $this->createMock(SubjectExchange::class);
        $this->storeMock = $this->createMock(Store::class);

        $this->envelopeFactoryMock = $this->createMock(EnvelopeFactory::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->loggerMock = $this->getMockForAbstractClass(\Psr\Log\LoggerInterface::class);

        $objectManager = new ObjectManager($this);
        $this->exchangePlugin = $objectManager->getObject(
            Exchange::class,
            [
                'envelopeFactory' => $this->envelopeFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'logger' => $this->loggerMock,
            ]
        );
    }

    public function testBeforeEnqueueWhenCanNotGetCurrentStoreId()
    {
        $topic = 'test_topic';
        $envelopes = [];

        $message = 'no_such_entity_exception';
        $this->storeManagerMock
            ->expects($this->once())
            ->method('getStore')
            ->willThrowException(new NoSuchEntityException(__($message)));
        $this->loggerMock
            ->expects($this->once())
            ->method('error')
            ->with("Can't get current storeId and inject to amqp message. Error $message.");

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("Can't get current storeId and inject to amqp message. Error $message.");

        $this->exchangePlugin->beforeEnqueue(
            $this->subjectExchange,
            $topic,
            $envelopes
        );
    }

    public function testBeforeEnqueueWhenEnvelopePropertiesNull()
    {
        $topic = 'test_topic';
        $envelope_1 = $this->getMockForAbstractClass(EnvelopeInterface::class);
        $envelopes = [$envelope_1];
        $storeId = 123;

        $this->prepareMocksToGetStoreId($storeId);

        $envelope_1
            ->expects($this->once())
            ->method('getProperties');

        $body = 'envelope_body';
        $envelope_1
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($body);

        $newEnvelope = $this->getMockForAbstractClass(EnvelopeInterface::class);
        $this->envelopeFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(
                [
                    'body' => $body,
                    'properties' => ['application_headers' => new AMQPTable(['store_id' => $storeId])]
                ]
            )->willReturn($newEnvelope);

        $actualResult = $this->exchangePlugin->beforeEnqueue(
            $this->subjectExchange,
            $topic,
            $envelopes
        );

        $this->assertSame($topic, $actualResult[0]);
        $this->assertSame($newEnvelope, $actualResult[1][0]);
    }

    public function testBeforeEnqueueWhenHeaderIsAmqpTableButCanNotSetStoreId()
    {
        $topic = 'test_topic_xxx';
        $envelope_1 = $this->getMockForAbstractClass(EnvelopeInterface::class);
        $envelopes = [$envelope_1];
        $storeId = 123;
        $headers = $this->createMock(AMQPTable::class);
        $properties = ['application_headers' => $headers];

        $this->prepareMocksToGetStoreId($storeId);

        $envelope_1
            ->expects($this->once())
            ->method('getProperties')
            ->willReturn($properties);

        $exceptionMessage = 'errrorrrr!';
        $headers
            ->expects($this->once())
            ->method('set')
            ->with('store_id', $storeId)
            ->willThrowException(new AMQPInvalidArgumentException($exceptionMessage));
        $this->loggerMock
            ->expects($this->once())
            ->method('error')
            ->with("Can't set storeId to amqp message. Error $exceptionMessage.");

        $this->expectException(AMQPInvalidArgumentException::class);
        $this->expectExceptionMessage("Can't set storeId to amqp message. Error $exceptionMessage.");

        $this->exchangePlugin->beforeEnqueue(
            $this->subjectExchange,
            $topic,
            $envelopes
        );
    }

    public function testBeforeEnqueueWhenIsAmqpTableAndSuccess()
    {
        $topic = 'test_topic';
        $envelope_1 = $this->getMockForAbstractClass(EnvelopeInterface::class);
        $envelopes = [$envelope_1];
        $storeId = 999;
        $headers = $this->createMock(AMQPTable::class);
        $properties = ['application_headers' => $headers];

        $this->prepareMocksToGetStoreId($storeId);

        $envelope_1
            ->expects($this->once())
            ->method('getProperties')
            ->willReturn($properties);
        $headers
            ->expects($this->once())
            ->method('set')
            ->with('store_id', $storeId);

        $body = 'envelope_body';
        $envelope_1
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($body);

        $newEnvelope = $this->getMockForAbstractClass(EnvelopeInterface::class);
        $this->envelopeFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(
                [
                    'body' => $body,
                    'properties' => ['application_headers' => $headers]
                ]
            )->willReturn($newEnvelope);

        $actualResult = $this->exchangePlugin->beforeEnqueue(
            $this->subjectExchange,
            $topic,
            $envelopes
        );

        $this->assertSame($topic, $actualResult[0]);
        $this->assertSame($newEnvelope, $actualResult[1][0]);
        $this->assertSame(1, count($actualResult[1]));
    }

    private function prepareMocksToGetStoreId(int $storeId)
    {
        $this->storeManagerMock
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
    }
}
