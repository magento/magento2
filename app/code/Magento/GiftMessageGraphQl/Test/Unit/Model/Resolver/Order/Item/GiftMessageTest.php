<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GiftMessageGraphQl\Test\Unit\Model\Resolver\Order\Item;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GiftMessage\Api\OrderItemRepositoryInterface;
use Magento\GiftMessageGraphQl\Model\Config\Messages;
use Magento\GiftMessageGraphQl\Model\Resolver\Order\Item\GiftMessage;
use Magento\Sales\Model\Order\Item as OrderItem;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test class for \Magento\GiftMessageGraphQl\Model\Resolver\Order\Item\GiftMessage
 *
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
     * @var OrderItemRepositoryInterface|MockObject
     */
    private OrderItemRepositoryInterface $orderItemRepositoryMock;

    /**
     * @var Messages|MockObject
     */
    private Messages $messagesConfigMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private LoggerInterface $loggerMock;

    /**
     * @var OrderItem|MockObject
     */
    private OrderItem $orderItemMock;

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
        $this->orderItemRepositoryMock = $this->createMock(OrderItemRepositoryInterface::class);
        $this->messagesConfigMock = $this->createMock(Messages::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->orderItemMock = $this->createMock(OrderItem::class);
        $this->giftMessage = new GiftMessage(
            $this->orderItemRepositoryMock,
            $this->messagesConfigMock,
            $this->loggerMock
        );
    }

    /**
     * @throws GraphQlInputException
     */
    public function testResolveWithoutModelInValueParameter(): void
    {
        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('"model" value must be specified');
        $this->giftMessage->resolve($this->fieldMock, $this->contextMock, $this->resolveInfoMock, $this->valueMock);
    }

    public function testResolveReturnsNullWithoutLoggingWhenNoSuchEntityException(): void
    {
        $this->valueMock = ['model' => $this->orderItemMock];
        $this->messagesConfigMock
            ->method('isMessagesAllowed')
            ->willReturn(true);

        $this->orderItemMock
            ->method('getOrderId')
            ->willReturn(10);
        $this->orderItemMock
            ->method('getItemId')
            ->willReturn(20);

        $this->orderItemRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with(10, 20)
            ->willThrowException(new NoSuchEntityException(__('No item with the provided ID was found.')));

        $this->loggerMock
            ->expects($this->never())
            ->method($this->anything());

        $this->assertNull(
            $this->giftMessage->resolve($this->fieldMock, $this->contextMock, $this->resolveInfoMock, $this->valueMock)
        );
    }

    public function testResolveLogsErrorForLocalizedException(): void
    {
        $this->valueMock = ['model' => $this->orderItemMock];
        $this->messagesConfigMock
            ->method('isMessagesAllowed')
            ->willReturn(true);

        $this->orderItemMock
            ->method('getOrderId')
            ->willReturn(10);
        $this->orderItemMock
            ->method('getItemId')
            ->willReturn(20);

        $this->orderItemRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with(10, 20)
            ->willThrowException(new LocalizedException(__('Something went wrong.')));

        $this->loggerMock
            ->expects($this->once())
            ->method('error');

        $this->assertNull(
            $this->giftMessage->resolve($this->fieldMock, $this->contextMock, $this->resolveInfoMock, $this->valueMock)
        );
    }

    public function testResolveReturnsNullWhenMessagesNotAllowed(): void
    {
        $this->valueMock = ['model' => $this->orderItemMock];
        $this->messagesConfigMock
            ->expects($this->once())
            ->method('isMessagesAllowed')
            ->with('items', $this->orderItemMock)
            ->willReturn(false);

        $this->orderItemRepositoryMock
            ->expects($this->never())
            ->method('get');

        $this->loggerMock
            ->expects($this->never())
            ->method($this->anything());

        $this->assertNull(
            $this->giftMessage->resolve($this->fieldMock, $this->contextMock, $this->resolveInfoMock, $this->valueMock)
        );
    }
}
