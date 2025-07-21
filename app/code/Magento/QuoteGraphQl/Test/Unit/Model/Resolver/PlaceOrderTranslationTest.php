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
use Magento\GraphQl\Helper\Error\AggregateExceptionMessageFormatter;
use Magento\GraphQl\Model\Query\Context;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Cart\GetCartForCheckout;
use Magento\QuoteGraphQl\Model\Cart\PlaceOrder as PlaceOrderModel;
use Magento\QuoteGraphQl\Model\ErrorMapper;
use Magento\QuoteGraphQl\Model\QuoteException;
use Magento\QuoteGraphQl\Model\Resolver\PlaceOrder;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\SalesGraphQl\Model\Formatter\Order as OrderFormatter;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PlaceOrderTranslationTest extends TestCase
{
    /**
     * @var GetCartForCheckout|MockObject
     */
    private $getCartForCheckoutMock;

    /**
     * @var PlaceOrderModel|MockObject
     */
    private $placeOrderModelMock;

    /**
     * @var AggregateExceptionMessageFormatter|MockObject
     */
    private $errorMessageFormatterMock;

    /**
     * @var ErrorMapper|MockObject
     */
    private $errorMapperMock;

    /**
     * @var PlaceOrder
     */
    private $placeOrderResolver;

    protected function setUp(): void
    {
        $this->getCartForCheckoutMock = $this->createMock(GetCartForCheckout::class);
        $this->placeOrderModelMock = $this->createMock(PlaceOrderModel::class);
        $this->errorMessageFormatterMock = $this->createMock(AggregateExceptionMessageFormatter::class);
        $this->errorMapperMock = $this->createMock(ErrorMapper::class);

        $this->placeOrderResolver = new PlaceOrder(
            $this->getCartForCheckoutMock,
            $this->placeOrderModelMock,
            $this->createMock(OrderRepositoryInterface::class),
            $this->createMock(OrderFormatter::class),
            $this->errorMessageFormatterMock,
            $this->errorMapperMock
        );
    }

    /**
     * Test that getRawMessage() is called on GraphQlInputException to map the error message properly.
     */
    public function testGetRawMessageIsCalledForErrorMapping(): void
    {
        $exception = $this->getMockBuilder(GraphQlInputException::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRawMessage'])
            ->getMock();
        $exception->method('getRawMessage')->willReturn('Raw error message');
        $exception->expects($this->once())->method('getRawMessage');

        $this->errorMapperMock->expects($this->once())
            ->method('getErrorMessageId')
            ->with('Raw error message')
            ->willReturn(1);

        $this->getCartForCheckoutMock->method('execute')->willReturn($this->createMock(Quote::class));
        $this->placeOrderModelMock->method('execute')->willThrowException($exception);
        $this->errorMessageFormatterMock->method('getFormatted')->willReturn($exception);

        $contextMock = $this->createMock(Context::class);

        $extensionAttributesMock = $this->getMockBuilder(ContextExtensionInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(
                [
                    'getStore',
                ]
            )
            ->getMock();
        $extensionAttributesMock->method('getStore')->willReturn($this->createMock(StoreInterface::class));
        $contextMock->method('getExtensionAttributes')->willReturn($extensionAttributesMock);

        $this->expectException(QuoteException::class);
        $this->placeOrderResolver->resolve(
            $this->createMock(Field::class),
            $contextMock,
            $this->createMock(ResolveInfo::class),
            null,
            ['input' => ['cart_id' => 'masked_cart_id']]
        );
    }
}
