<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Test\Unit\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\Context;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Cart\GetCartForCheckout;
use Magento\QuoteGraphQl\Model\Cart\PlaceOrder as PlaceOrderModel;
use Magento\QuoteGraphQl\Model\OrderErrorProcessor;
use Magento\QuoteGraphQl\Model\QuoteException;
use Magento\QuoteGraphQl\Model\Resolver\PlaceOrder;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\SalesGraphQl\Model\Formatter\Order as OrderFormatter;
use Magento\Store\Api\Data\StoreInterface;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PlaceOrderExceptionProcessingTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var GetCartForCheckout|MockObject
     */
    private $getCartForCheckoutMock;

    /**
     * @var PlaceOrderModel|MockObject
     */
    private $placeOrderModelMock;

    /**
     * @var OrderErrorProcessor|MockObject
     */
    private $orderErrorProcessor;

    /**
     * @var PlaceOrder
     */
    private $placeOrderResolver;

    protected function setUp(): void
    {
        $this->getCartForCheckoutMock = $this->createMock(GetCartForCheckout::class);
        $this->placeOrderModelMock = $this->createMock(PlaceOrderModel::class);
        $this->orderErrorProcessor = $this->createMock(OrderErrorProcessor::class);

        $this->placeOrderResolver = new PlaceOrder(
            $this->getCartForCheckoutMock,
            $this->placeOrderModelMock,
            $this->createMock(OrderRepositoryInterface::class),
            $this->createMock(OrderFormatter::class),
            $this->orderErrorProcessor
        );
    }

    /**
     * Test that OrderErrorProcessor::execute method is being triggered on thrown LocalizedException
     */
    public function testExceptionProcessing(): void
    {
        $exception = $this->createMock(GraphQlInputException::class);
        $this->getCartForCheckoutMock->method('execute')->willReturn($this->createMock(Quote::class));
        $this->placeOrderModelMock->method('execute')->willThrowException($exception);

        $contextMock = $this->createMock(Context::class);
        $extAttrs = $this->createPartialMockWithReflection(
            ContextExtensionInterface::class,
            ['setStore', 'getStore']
        );
        $extAttrs->method('getStore')->willReturn($this->createMock(StoreInterface::class));
        $contextMock->method('getExtensionAttributes')->willReturn($extAttrs);

        $field = $this->createMock(Field::class);
        $info = $this->createMock(ResolveInfo::class);
        $this->orderErrorProcessor->expects($this->once())
            ->method('execute')
            ->with($exception, $field, $contextMock)
            ->willThrowException($this->createMock(QuoteException::class));

        $this->expectException(QuoteException::class);
        $this->placeOrderResolver->resolve(
            $this->createMock(Field::class),
            $contextMock,
            $info,
            null,
            ['input' => ['cart_id' => 'masked_cart_id']]
        );
    }
}
