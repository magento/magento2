<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\AddProductToCart;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Quote\Api\CartRepositoryInterfaceFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteMutexInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class AddProductToCartTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var QuoteMutexInterface|MockObject
     */
    private $quoteMutex;

    /**
     * @var CartRepositoryInterfaceFactory|MockObject
     */
    private $quoteRepositoryFactory;

    /**
     * @var DateTime|MockObject
     */
    private $datetime;

    /**
     * @var AddProductToCart
     */
    private $addProductToCart;

    protected function setUp(): void
    {
        $this->quoteMutex = $this->createMock(QuoteMutexInterface::class);
        $this->quoteRepositoryFactory = $this->createMock(CartRepositoryInterfaceFactory::class);
        $this->datetime = $this->createMock(DateTime::class);
        $this->addProductToCart = new AddProductToCart(
            $this->quoteMutex,
            $this->quoteRepositoryFactory,
            $this->datetime
        );
    }

    public function testExecuteAddsProductToCartWhenQuoteIdIsNotSet(): void
    {
        $cart = $this->createMock(Cart::class);
        $product = $this->createMock(Product::class);
        $quote = $this->createMock(Quote::class);
        $buyRequest = ['qty' => 1];
        $related = [2, 3];

        $cart->method('getQuote')->willReturn($quote);
        $cart->getQuote()->method('getId')->willReturn(null);
        $cart->expects($this->once())->method('addProduct')->with($product, $buyRequest);
        $cart->expects($this->once())->method('addProductsByIds')->with($related);
        $cart->expects($this->once())->method('save');
        $this->quoteMutex->expects($this->never())->method('execute');

        $this->assertTrue($this->addProductToCart->execute($cart, $product, $buyRequest, $related));
    }

    #[
        DataProvider('executeDataProvider')
    ]
    public function testExecuteAddsProductToCartWhenQuoteIdIsSet(
        array $currentQuoteData,
        array $updatedQuoteData,
        bool $isQuoteReloaded,
        ?callable $mutex = null
    ): void {
        $cart = $this->createMock(Cart::class);
        $checkoutSession = $this->createMock(Session::class);
        $product = $this->createMock(Product::class);
        $currentQuote = $this->createMock(Quote::class);
        $updatedQuote = $this->createMock(Quote::class);
        $reloadedQuote = $this->createMock(Quote::class);
        $buyRequest = ['qty' => 1];
        $related = [2, 3];
        $quoteId = 1;

        $cart->method('getQuote')->willReturn($currentQuote);
        $cart->method('getCheckoutSession')->willReturn($checkoutSession);
        $currentQuote->method('getId')->willReturn($quoteId);
        foreach ($currentQuoteData as $getter => $return) {
            $currentQuote->method($getter)->willReturn($return);
        }
        foreach ($updatedQuoteData as $getter => $return) {
            $updatedQuote->method($getter)->willReturn($return);
        }
        $this->datetime->method('timestamp')
            ->willReturnCallback(fn (?string $str = null) => $str ? strtotime($str) : time());
        $this->quoteRepositoryFactory
            ->expects($this->exactly($isQuoteReloaded ? 1 : 0))
            ->method('create')
            ->willReturnCallback(
                fn () => $this->createConfiguredMock(CartRepositoryInterface::class, ['getActive' => $reloadedQuote])
            );

        $cart->expects($this->exactly($isQuoteReloaded ? 1 : 0))->method('setQuote')->with($reloadedQuote);
        $checkoutSession->expects($this->exactly($isQuoteReloaded ? 1 : 0))
            ->method('replaceQuote')
            ->with($reloadedQuote);
        $cart->expects($this->once())->method('addProduct')->with($product, $buyRequest);
        $cart->expects($this->once())->method('addProductsByIds')->with($related);
        $cart->expects($this->once())->method('save');
        $this->quoteMutex->expects($this->once())
            ->method('execute')
            ->with([$quoteId], $this->callback(is_callable(...)))
            ->willReturnCallback($mutex ?? fn ($ids, $callback) => $callback([$updatedQuote]));

        $this->assertTrue($this->addProductToCart->execute($cart, $product, $buyRequest, $related));
    }

    public static function executeDataProvider(): array
    {
        return [
            'quote is reloaded - 1' => [
                ['getUpdatedAt' => '2024-01-01 00:00:00'],
                ['getUpdatedAt' => '2024-01-01 00:00:01'],
                true,
            ],
            'quote is reloaded - 2' => [
                ['getOrigData' => '2024-01-01 00:00:00'],
                ['getUpdatedAt' => '2024-01-01 00:00:01'],
                true,
            ],
            'quote is reloaded - 3' => [
                ['getOrigData' => null],
                ['getUpdatedAt' => null],
                true,
                fn ($ids, $callback) => $callback()
            ],
            'quote is not reloaded - 1' => [
                ['getUpdatedAt' => null],
                ['getUpdatedAt' => '2024-01-01 00:00:00'],
                false,
            ],
            'quote is not reloaded - 2' => [
                ['getUpdatedAt' => '2024-01-01 00:00:00'],
                ['getUpdatedAt' => null],
                false,
            ],
            'quote is not reloaded - 3' => [
                ['getUpdatedAt' => null],
                ['getUpdatedAt' => null],
                false,
            ],
            'quote is not reloaded - 4' => [
                ['getUpdatedAt' => '2024-01-01 00:00:00'],
                ['getUpdatedAt' => '2024-01-01 00:00:00'],
                false,
            ],
        ];
    }
}
