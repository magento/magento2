<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GiftMessageGraphQl\Test\Unit\Model\Resolver\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GiftMessage\Api\Data\MessageInterface;
use Magento\GiftMessage\Api\OrderRepositoryInterface;
use Magento\GiftMessageGraphQl\Model\Resolver\Order\GiftMessage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GiftMessageTest extends TestCase
{
    /**
     * @var GiftMessage
     */
    private GiftMessage $giftMessage;

    /**
     * @var Field|MockObject
     */
    private Field $fieldMock;

    /**
     * @var ContextInterface|MockObject
     */
    private ContextInterface $contextMock;

    /**
     * @var ResolverInterface|MockObject
     */
    private ResolverInterface $resolverMock;

    /**
     * @var ResolveInfo|MockObject
     */
    private ResolveInfo $resolveInfoMock;

    /**
     * @var OrderRepositoryInterface|MockObject
     */
    private OrderRepositoryInterface $orderRepositoryMock;

    /**
     * @var MessageInterface|MockObject
     */
    private MessageInterface $messageMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private LoggerInterface $loggerMock;

    /**
     * @var array
     */
    private array $valueMock = [];

    protected function setUp(): void
    {
        $this->fieldMock = $this->createMock(Field::class);
        $this->contextMock = $this->createMock(ContextInterface::class);
        $this->resolverMock = $this->createMock(ResolverInterface::class);
        $this->resolveInfoMock = $this->createMock(ResolveInfo::class);
        $this->orderRepositoryMock =$this->createMock(OrderRepositoryInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $uidEncoder = $this->createMock(Uid::class);
        $this->messageMock = $this->createMock(MessageInterface::class);
        $this->giftMessage = new GiftMessage(
            $this->orderRepositoryMock,
            $this->loggerMock,
            $uidEncoder
        );
    }

    /**
     * @throws GraphQlInputException
     */
    public function testResolveWithoutIDInValueParameter(): void
    {
        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('"id" value should be specified');
        $this->giftMessage->resolve($this->fieldMock, $this->contextMock, $this->resolveInfoMock, $this->valueMock);
    }

    /**
     * @throws GraphQlInputException
     */
    public function testResolve(): void
    {
        $this->valueMock = ['id' => "111"];
        $this->orderRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->willReturn($this->messageMock);

        $this->messageMock
            ->expects($this->once())
            ->method('getGiftMessageId')
            ->willReturn(null);

        $this->assertEquals(
            null,
            $this->giftMessage->resolve($this->fieldMock, $this->contextMock, $this->resolveInfoMock, $this->valueMock)
        );
    }

    /**
     * @return array|Value|mixed
     * @throws GraphQlInputException
     */
    public function testResolveWithMessageId(): void
    {
        $this->valueMock = ['id' => "112"];
        $this->orderRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->willReturn($this->messageMock);

        $this->messageMock
            ->expects($this->once())
            ->method('getGiftMessageId')
            ->willReturn(1);

        $this->assertEquals(
            [
                'to' => '',
                'from' => '',
                'message' =>''
            ],
            $this->giftMessage->resolve($this->fieldMock, $this->contextMock, $this->resolveInfoMock, $this->valueMock)
        );
    }

    public function testResolveReturnsNullWithoutLoggingWhenNoSuchEntityException(): void
    {
        $this->valueMock = ['id' => "113"];
        $this->orderRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->willThrowException(new NoSuchEntityException(__('Gift message is not allowed.')));

        $this->loggerMock
            ->expects($this->never())
            ->method($this->anything());

        $this->assertNull(
            $this->giftMessage->resolve($this->fieldMock, $this->contextMock, $this->resolveInfoMock, $this->valueMock)
        );
    }

    public function testResolveLogsErrorWithContextForLocalizedException(): void
    {
        $this->valueMock = ['id' => base64_encode('42')];
        $exception = new LocalizedException(__('Something went wrong.'));
        $this->orderRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->willThrowException($exception);

        $this->loggerMock
            ->expects($this->once())
            ->method('error')
            ->with(
                __('Can\'t load gift message for order'),
                $this->callback(function (array $context) use ($exception): bool {
                    return array_key_exists('order_id', $context)
                        && $context['order_id'] === 42
                        && array_key_exists('exception', $context)
                        && $context['exception'] === $exception;
                })
            );

        $this->assertNull(
            $this->giftMessage->resolve($this->fieldMock, $this->contextMock, $this->resolveInfoMock, $this->valueMock)
        );
    }

    public function testResolveReturnsGiftMessageData(): void
    {
        $this->valueMock = ['id' => "114"];
        $this->orderRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->willReturn($this->messageMock);

        $this->messageMock
            ->expects($this->once())
            ->method('getGiftMessageId')
            ->willReturn(1);

        $this->messageMock
            ->method('getRecipient')
            ->willReturn('recipient');

        $this->messageMock
            ->method('getSender')
            ->willReturn('sender');

        $this->messageMock
            ->method('getMessage')
            ->willReturn('message');

        $this->assertEquals(
            [
                'to' => 'recipient',
                'from' => 'sender',
                'message' => 'message'
            ],
            $this->giftMessage->resolve($this->fieldMock, $this->contextMock, $this->resolveInfoMock, $this->valueMock)
        );
    }
}
