<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Plugin\Checkout\CustomerData;

use Magento\Checkout\CustomerData\Cart as CheckoutCart;
use Magento\Checkout\Helper\Data;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Tax\Block\Item\Price\Renderer;
use Magento\Tax\Plugin\Checkout\CustomerData\Cart;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CartTest extends TestCase
{
    /**
     * @var Session|MockObject
     */
    private $checkoutSession;

    /**
     * @var Data|MockObject
     */
    private $checkoutHelper;

    /**
     * @var Renderer|MockObject
     */
    private $itemPriceRenderer;

    /**
     * @var CheckoutCart|MockObject
     */
    private $checkoutCart;

    /**
     * @var Quote|MockObject
     */
    private $quote;

    /**
     * @var Cart
     */
    private $cart;

    protected function setUp(): void
    {
        $this->checkoutSession = $this->createMock(Session::class);
        $this->checkoutHelper = $this->createMock(Data::class);
        $this->itemPriceRenderer = $this->createMock(Renderer::class);
        $this->checkoutCart = $this->createMock(CheckoutCart::class);
        $this->quote = $this->createMock(Quote::class);

        $this->checkoutSession->method('getQuote')
            ->willReturn($this->quote);

        $this->cart = new Cart(
            $this->checkoutSession,
            $this->checkoutHelper,
            $this->itemPriceRenderer
        );
    }

    public function testAfterGetSectionData()
    {
        $input = ['items' => [
            [
                'item_id' => 1,
                'product_price' => ''
            ],
            [
                'item_id' => 2,
                'product_price' => ''
            ],
        ]
        ];

        $this->checkoutHelper->method('formatPrice')
            ->willReturn('formatted');

        $item1 = $this->createMock(Item::class);
        $item2 = $this->createMock(Item::class);

        $item1->method('getItemId')
            ->willReturn(1);
        $item2->method('getItemId')
            ->willReturn(2);

        $this->quote->method('getAllVisibleItems')
            ->willReturn([
                $item1,
                $item2,
            ]);

        $this->itemPriceRenderer->method('toHtml')
            ->willReturn(1);

        $result = $this->cart->afterGetSectionData($this->checkoutCart, $input);

        self::assertArrayHasKey('subtotal_incl_tax', $result);
        self::assertArrayHasKey('subtotal_excl_tax', $result);
        self::assertArrayHasKey('items', $result);
        self::assertIsArray($result['items']);
        self::assertCount(2, $result['items']);
        self::assertEquals(1, $result['items'][0]['product_price']);
        self::assertEquals(1, $result['items'][1]['product_price']);
    }
}
