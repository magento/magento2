<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Test\Unit\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\GraphQl\Helper\Error\AggregateExceptionMessageFormatter;
use Magento\GraphQl\Model\Query\Context;
use Magento\GraphQl\Model\Query\ContextExtension;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\QuoteGraphQl\Model\Cart\PlaceOrder as PlaceOrderModel;
use Magento\QuoteGraphQl\Model\Resolver\PlaceOrder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see PlaceOrder
 */
class PlaceOrderTest extends TestCase
{
    /**
     * @var PlaceOrder
     */
    private $model;

    /**
     * @var GetCartForUser|MockObject
     */
    private $getCartForUser;

    /**
     * @var PlaceOrderModel|MockObject
     */
    private $placeOrder;

    /**
     * @var OrderRepositoryInterface|MockObject
     */
    private $orderRepository;

    /**
     * @var AggregateExceptionMessageFormatter|MockObject
     */
    private $errorMessageFormatter;

    /**
     * @var LockManagerInterface|MockObject
     */
    private $lockManager;

    /**
     * @var Field|MockObject
     */
    private $fieldMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var ResolveInfo|MockObject
     */
    private $resolveInfoMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->getCartForUser = $this->createMock(GetCartForUser::class);
        $this->placeOrder = $this->createMock(PlaceOrderModel::class);
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->errorMessageFormatter = $this->createMock(AggregateExceptionMessageFormatter::class);
        $this->lockManager = $this->createMock(LockManagerInterface::class);
        $this->fieldMock = $this->createMock(Field::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->resolveInfoMock = $this->createMock(ResolveInfo::class);

        $this->model = new PlaceOrder(
            $this->getCartForUser,
            $this->placeOrder,
            $this->orderRepository,
            $this->errorMessageFormatter,
            $this->lockManager
        );
    }

    /**
     * @return void
     */
    public function testResolveDuplicate(): void
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $argsMock = [
            'input' => [
                'cart_id' => '1'
            ]
        ];
        $storeMock = $this->createMock(Store::class);
        $extensionAttributesMock = $this->createMock(ContextExtension::class);
        $extensionAttributesMock
            ->expects($this->any())
            ->method('getStore')
            ->willReturn($storeMock);
        $orderMock = $this->createMock(OrderInterface::class);

        $this->contextMock
            ->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributesMock);
        $this->lockManager
            ->expects($this->exactly(2))
            ->method('isLocked')
            ->willReturnOnConsecutiveCalls(
                false,
                true
            );
        $this->orderRepository
            ->expects($this->any())
            ->method('get')
            ->willReturn($orderMock);

        $this->model->resolve($this->fieldMock, $this->contextMock, $this->resolveInfoMock, [], $argsMock);
        $this->model->resolve($this->fieldMock, $this->contextMock, $this->resolveInfoMock, [], $argsMock);
    }
}
