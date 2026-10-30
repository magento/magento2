<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AdminNotification\Test\Unit\Model\ResourceModel\System\Message\Collection;

use Magento\AdminNotification\Model\ResourceModel\System\Message\Collection\Synchronized;
use Magento\AdminNotification\Model\System\Message;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\Notification\MessageList;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use RuntimeException;

class SynchronizedTest extends TestCase
{
    private const MESSAGE_IDENTITY = 'test_message';
    private const MESSAGE_SEVERITY = MessageInterface::SEVERITY_NOTICE;

    /**
     * Verify a message inserted by a concurrent request does not break collection synchronization.
     */
    public function testAfterLoadIgnoresMessageInsertedByConcurrentRequest(): void
    {
        $message = $this->createMessage();
        $newItem = $this->createSystemMessage();
        $newItem->expects(self::once())
            ->method('save')
            ->willThrowException(new AlreadyExistsException());
        $collection = $this->createCollection($message, $newItem);
        $collection->expects(self::once())->method('clear')->willReturnSelf();
        $collection->expects(self::once())->method('load')->willReturnSelf();

        self::assertSame($collection, $collection->_afterLoad());
        self::assertSame([$message], $collection->getUnread());
    }

    /**
     * Verify unrelated persistence failures are not suppressed.
     */
    public function testAfterLoadRethrowsUnexpectedSaveException(): void
    {
        $exception = new RuntimeException('Unexpected persistence failure');
        $newItem = $this->createSystemMessage();
        $newItem->expects(self::once())->method('save')->willThrowException($exception);
        $collection = $this->createCollection($this->createMessage(), $newItem);
        $collection->expects(self::never())->method('clear');
        $collection->expects(self::never())->method('load');

        $this->expectExceptionObject($exception);

        $collection->_afterLoad();
    }

    /**
     * Create a displayed system-message provider.
     */
    private function createMessage(): MessageInterface
    {
        $message = $this->createStub(MessageInterface::class);
        $message->method('isDisplayed')->willReturn(true);
        $message->method('getIdentity')->willReturn(self::MESSAGE_IDENTITY);
        $message->method('getSeverity')->willReturn(self::MESSAGE_SEVERITY);

        return $message;
    }

    /**
     * Create the system-message model used for the insert attempt.
     */
    private function createSystemMessage(): Message&MockObject
    {
        $item = $this->getMockBuilder(Message::class)
            ->onlyMethods(['save', '__call'])
            ->disableOriginalConstructor()
            ->getMock();
        $expectedCalls = [
            ['setIdentity', [self::MESSAGE_IDENTITY]],
            ['setSeverity', [self::MESSAGE_SEVERITY]],
        ];
        $item->expects(self::exactly(2))
            ->method('__call')
            ->willReturnCallback(
                static function (string $method, array $arguments) use (&$expectedCalls, $item): Message {
                    self::assertSame(array_shift($expectedCalls), [$method, $arguments]);

                    return $item;
                }
            );

        return $item;
    }

    /**
     * Create a synchronized collection around the supplied message and model.
     */
    private function createCollection(
        MessageInterface $message,
        Message $newItem
    ): Synchronized&MockObject {
        $messageList = $this->createStub(MessageList::class);
        $messageList->method('asArray')->willReturn([$message]);
        $collection = $this->getMockBuilder(Synchronized::class)
            ->onlyMethods(['getNewEmptyItem', 'clear', 'load'])
            ->disableOriginalConstructor()
            ->getMock();
        $collection->method('getNewEmptyItem')->willReturn($newItem);

        $messageListProperty = new ReflectionProperty($collection, '_messageList');
        $messageListProperty->setValue($collection, $messageList);
        $itemsProperty = new ReflectionProperty($collection, '_items');
        $itemsProperty->setValue($collection, []);

        return $collection;
    }
}
