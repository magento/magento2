<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Rule\Action\Discount;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Multishipping\Model\Checkout\Type\Multishipping;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Test\Fixture\Rule as RuleFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

#[
    AppArea('frontend'),
]
class ByPercentTest extends TestCase
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    protected function setUp(): void
    {
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->quoteRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
        $this->orderRepository = Bootstrap::getObjectManager()->get(OrderRepositoryInterface::class);
    }

    #[
        DataFixture(
            RuleFixture::class,
            ['simple_action' => Rule::BY_PERCENT_ACTION, 'discount_amount' => 10, 'coupon_code' => 'COUPON10']
        ),
        DataFixture('Magento/Multishipping/Fixtures/quote_with_split_items.php'),
    ]
    public function testMultishipping(): void
    {
        $quote = $this->getQuote('multishipping_quote_id');
        $quote->setCouponCode('COUPON10');
        $quote->collectTotals();
        $this->quoteRepository->save($quote);

        $session = Bootstrap::getObjectManager()->get(CheckoutSession::class);
        $session->replaceQuote($quote);
        $multishipping = Bootstrap::getObjectManager()->get(Multishipping::class);
        $multishipping->createOrders();
        $orderList = array_values($this->getOrderList((int) $quote->getId()));
        self::assertCount(3, $orderList);

        foreach ($orderList as $order) {
            self::assertNotEmpty($order->getAppliedRuleIds());
            $discountAmount = $order->getSubtotal() * 0.1;
            self::assertEquals($discountAmount * -1, $order->getDiscountAmount());
            $grandTotal = $order->getSubtotal() + $order->getShippingAmount() - $discountAmount;
            self::assertEquals($grandTotal, $order->getGrandTotal());
            $orderItem = array_values($order->getItems())[0];
            self::assertNotEmpty($orderItem->getAppliedRuleIds());
            self::assertEquals($discountAmount, $orderItem->getDiscountAmount());
        }
    }

    /**
     * Load cart from fixture.
     *
     * @param string $reservedOrderId
     * @return Quote
     */
    private function getQuote(string $reservedOrderId): Quote
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('reserved_order_id', $reservedOrderId)
            ->create();
        $quoteList = $this->quoteRepository->getList($searchCriteria);

        return array_values($quoteList->getItems())[0];
    }

    /**
     * Get list of orders by quote id.
     *
     * @param int $quoteId
     * @return Order[]
     */
    private function getOrderList(int $quoteId): array
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('quote_id', $quoteId)->create();
        $orderList = $this->orderRepository->getList($searchCriteria);

        return $orderList->getItems();
    }
}
