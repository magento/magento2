<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Rule\Action\Discount;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Multishipping\Model\Checkout\Type\Multishipping;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\GuestCartItemRepositoryInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Api\GuestCartTotalRepositoryInterface;
use Magento\Quote\Api\GuestCouponManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\Rule;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Tests for Magento\SalesRule\Model\Rule\Action\Discount\CartFixed.
 *
 * @magentoAppArea frontend
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartFixedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GuestCartManagementInterface
     */
    private $cartManagement;

    /**
     * @var GuestCartItemRepositoryInterface
     */
    private $cartItemRepository;

    /**
     * @var GuestCouponManagementInterface
     */
    private $couponManagement;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->cartManagement = $objectManager->create(GuestCartManagementInterface::class);
        $this->couponManagement = $objectManager->create(GuestCouponManagementInterface::class);
        $this->cartItemRepository = $objectManager->create(GuestCartItemRepositoryInterface::class);
        $this->criteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $this->quoteRepository = $objectManager->get(CartRepositoryInterface::class);
        $this->objectManager = $objectManager;
    }

    /**
     * Applies fixed discount amount on whole cart.
     *
     * @param array $productPrices
     * @return void
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/SalesRule/_files/coupon_cart_fixed_discount.php
     * @dataProvider applyFixedDiscountDataProvider
     */
    public function testApplyFixedDiscount(array $productPrices): void
    {
        $expectedDiscount = '-15.0000';
        $couponCode =  'CART_FIXED_DISCOUNT_15';
        $cartId = $this->cartManagement->createEmptyCart();

        foreach ($productPrices as $price) {
            $product = $this->createProduct($price);

            /** @var CartItemInterface $quoteItem */
            $quoteItem = Bootstrap::getObjectManager()->create(CartItemInterface::class);
            $quoteItem->setQuoteId($cartId);
            $quoteItem->setProduct($product);
            $quoteItem->setQty(1);
            $this->cartItemRepository->save($quoteItem);
        }

        $this->couponManagement->set($cartId, $couponCode);

        /** @var GuestCartTotalRepositoryInterface $cartTotalRepository */
        $cartTotalRepository = Bootstrap::getObjectManager()->get(GuestCartTotalRepositoryInterface::class);
        $total = $cartTotalRepository->get($cartId);

        $this->assertEquals($expectedDiscount, $total->getBaseDiscountAmount());
    }

    /**
     * Applies fixed discount amount on whole cart and created order with it
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default_store carriers/freeshipping/active 1
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/SalesRule/_files/coupon_cart_fixed_subtotal_with_discount.php
     */
    public function testOrderWithFixedDiscount(): void
    {
        $expectedGrandTotal = 5;

        $quote = $this->getQuote();
        $quote->getShippingAddress()
            ->setShippingMethod('freeshipping_freeshipping')
            ->setCollectShippingRates(true);
        $quote->setCouponCode('CART_FIXED_DISCOUNT_15');
        $quote->collectTotals();
        $this->quoteRepository->save($quote);
        $this->assertEquals($expectedGrandTotal, $quote->getGrandTotal());

        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = $this->objectManager->create(QuoteIdMask::class);
        $quoteIdMask->load($quote->getId(), 'quote_id');
        Bootstrap::getInstance()->reinitialize();
        $cartManagement = Bootstrap::getObjectManager()->create(GuestCartManagementInterface::class);
        $cartManagement->placeOrder($quoteIdMask->getMaskedId());
        $order = $this->getOrder('test01');
        $this->assertEquals($expectedGrandTotal, $order->getGrandTotal());
    }

    /**
     * Applies fixed discount amount on whole cart and quote and checks the quote model for item discounts
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default_store carriers/freeshipping/active 1
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/SalesRule/_files/coupon_cart_fixed_subtotal_with_discount.php
     */
    public function testDiscountsOnQuoteWithFixedDiscount(): void
    {
        $quote = $this->getQuote();
        $quote->getShippingAddress()
            ->setShippingMethod('freeshipping_freeshipping')
            ->setCollectShippingRates(true);
        $quote->setCouponCode('CART_FIXED_DISCOUNT_15');
        $quote->collectTotals();
        $this->quoteRepository->save($quote);
        /** @var CartItemInterface $item */
        $item = $quote->getItems()[0];
        $quoteItemDiscounts = $item->getExtensionAttributes()->getDiscounts();
        $this->assertArrayHasKey('0', $quoteItemDiscounts);
        $discountData = $quoteItemDiscounts[0]->getDiscountData();
        $ruleLabel = $quoteItemDiscounts[0]->getRuleLabel();
        $this->assertEquals(5, $discountData->getAmount());
        $this->assertEquals(5, $discountData->getBaseAmount());
        $this->assertEquals(5, $discountData->getOriginalAmount());
        $this->assertEquals(10, $discountData->getBaseOriginalAmount());
        $this->assertEquals('TestRule_Coupon', $ruleLabel);

        $quoteAddressItemDiscount = $quote->getShippingAddressesItems()[0]->getExtensionAttributes()->getDiscounts();
        $this->assertArrayHasKey('0', $quoteAddressItemDiscount);
        $discountData = $quoteAddressItemDiscount[0]->getDiscountData();
        $ruleLabel = $quoteAddressItemDiscount[0]->getRuleLabel();
        $this->assertEquals(5, $discountData->getAmount());
        $this->assertEquals(5, $discountData->getBaseAmount());
        $this->assertEquals(5, $discountData->getOriginalAmount());
        $this->assertEquals(10, $discountData->getBaseOriginalAmount());
        $this->assertEquals('TestRule_Coupon', $ruleLabel);
    }

    /**
     * Load cart from fixture.
     *
     * @param string $reservedOrderId
     * @return Quote
     */
    private function getQuote(string $reservedOrderId = 'test01'): Quote
    {
        $searchCriteria = $this->criteriaBuilder->addFilter('reserved_order_id', $reservedOrderId)->create();
        $carts = $this->quoteRepository->getList($searchCriteria)
            ->getItems();
        if (!$carts) {
            throw new \RuntimeException('Cart from fixture not found');
        }

        return array_shift($carts);
    }

    /**
     * @return array
     */
    public function applyFixedDiscountDataProvider(): array
    {
        return [
            'prices when discount had wrong value 15.01' => [[22, 14, 43, 7.50, 0.00]],
            'prices when discount had wrong value 14.99' => [[47, 33, 9.50, 42, 0.00]],
        ];
    }

    /**
     * Tests that coupon with wildcard symbols in code can be successfully applied.
     *
     * @magentoDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     */
    public function testCouponCodeWithWildcard()
    {
        $expectedDiscount = '-5.0000';
        $couponCode =  '2?ds5!2d';
        $cartId = $this->cartManagement->createEmptyCart();
        $productPrice = 10;

        $product = $this->createProduct($productPrice);

        /** @var CartItemInterface $quoteItem */
        $quoteItem = Bootstrap::getObjectManager()->create(CartItemInterface::class);
        $quoteItem->setQuoteId($cartId);
        $quoteItem->setProduct($product);
        $quoteItem->setQty(1);
        $this->cartItemRepository->save($quoteItem);

        $this->couponManagement->set($cartId, $couponCode);

        /** @var GuestCartTotalRepositoryInterface $cartTotalRepository */
        $cartTotalRepository = Bootstrap::getObjectManager()->get(GuestCartTotalRepositoryInterface::class);
        $total = $cartTotalRepository->get($cartId);

        $this->assertEquals($expectedDiscount, $total->getBaseDiscountAmount());
    }

    /**
     * Returns simple product with given price.
     *
     * @param float $price
     * @return ProductInterface
     */
    private function createProduct(float $price): ProductInterface
    {
        $name = 'simple-' . $price;
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepository::class);
        $product = Bootstrap::getObjectManager()->create(Product::class);
        $product->setTypeId('simple')
            ->setAttributeSetId($product->getDefaultAttributeSetId())
            ->setWebsiteIds([1])
            ->setName($name)
            ->setSku(uniqid($name))
            ->setPrice($price)
            ->setMetaTitle('meta title')
            ->setMetaKeyword('meta keyword')
            ->setMetaDescription('meta description')
            ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->setStockData(['qty' => 1, 'is_in_stock' => 1])
            ->setWeight(1);

        return $productRepository->save($product);
    }

    /**
     * Gets order entity by increment id.
     *
     * @param string $incrementId
     * @return OrderInterface
     */
    private function getOrder(string $incrementId): OrderInterface
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('increment_id', $incrementId)
            ->create();

        /** @var OrderRepositoryInterface $repository */
        $repository = $this->objectManager->get(OrderRepositoryInterface::class);
        $items = $repository->getList($searchCriteria)
            ->getItems();

        return array_pop($items);
    }

    /**
     * Checks "fixed amount discount for whole cart" with multiple orders with different shipping addresses
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/SalesRule/_files/coupon_cart_fixed_discount.php
     * @magentoDataFixture Magento/Multishipping/Fixtures/quote_with_split_items.php
     * @dataProvider multishippingDataProvider
     * @param float $discount
     * @param array $firstOrderTotals
     * @param array $secondOrderTotals
     * @param array $thirdOrderTotals
     * @return void
     */
    public function testMultishipping(
        float $discount,
        array $firstOrderTotals,
        array $secondOrderTotals,
        array $thirdOrderTotals
    ): void {
        $store = $this->objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore();
        $salesRule = $this->getRule('15$ fixed discount on whole cart');
        $salesRule->setDiscountAmount($discount);
        $this->saveRule($salesRule);
        $quote = $this->getQuote('multishipping_quote_id');
        $quote->setStoreId($store->getId());
        $quote->setCouponCode('CART_FIXED_DISCOUNT_15');
        $quote->collectTotals();
        $this->quoteRepository->save($quote);
        /** @var CheckoutSession $session */
        $session = $this->objectManager->get(CheckoutSession::class);
        $session->replaceQuote($quote);
        $orderSender = $this->getMockBuilder(OrderSender::class)
            ->disableOriginalConstructor()
            ->getMock();

        $model = $this->objectManager->create(
            Multishipping::class,
            ['orderSender' => $orderSender]
        );
        $model->createOrders();
        $orderList = $this->getOrderList((int)$quote->getId());
        $this->assertCount(3, $orderList);
        /**
         * The order with $10 simple product
         * @var Order $firstOrder
         */
        $firstOrder = array_shift($orderList);

        $this->assertEquals(
            $firstOrderTotals['subtotal'],
            $firstOrder->getSubtotal()
        );
        $this->assertEquals(
            $firstOrderTotals['discount_amount'],
            $firstOrder->getDiscountAmount()
        );
        $this->assertEquals(
            $firstOrderTotals['shipping_amount'],
            $firstOrder->getShippingAmount()
        );
        $this->assertEquals(
            $firstOrderTotals['grand_total'],
            $firstOrder->getGrandTotal()
        );
        /**
         * The order with $20 simple product
         * @var Order $secondOrder
         */
        $secondOrder = array_shift($orderList);
        $this->assertEquals(
            $secondOrderTotals['subtotal'],
            $secondOrder->getSubtotal()
        );
        $this->assertEquals(
            $secondOrderTotals['discount_amount'],
            $secondOrder->getDiscountAmount()
        );
        $this->assertEquals(
            $secondOrderTotals['shipping_amount'],
            $secondOrder->getShippingAmount()
        );
        $this->assertEquals(
            $secondOrderTotals['grand_total'],
            $secondOrder->getGrandTotal()
        );
        /**
         * The order with $5 virtual product and billing address as shipping
         * @var Order $thirdOrder
         */
        $thirdOrder = array_shift($orderList);
        $this->assertEquals(
            $thirdOrderTotals['subtotal'],
            $thirdOrder->getSubtotal()
        );
        $this->assertEquals(
            $thirdOrderTotals['discount_amount'],
            $thirdOrder->getDiscountAmount()
        );
        $this->assertEquals(
            $thirdOrderTotals['shipping_amount'],
            $thirdOrder->getShippingAmount()
        );
        $this->assertEquals(
            $thirdOrderTotals['grand_total'],
            $thirdOrder->getGrandTotal()
        );
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function multishippingDataProvider(): array
    {
        return [
            'Discount < 1stOrderSubtotal: only 1st order gets discount' => [
                5,
                [
                    'subtotal' => 10.00,
                    'discount_amount' => -5.00,
                    'shipping_amount' => 5.00,
                    'grand_total' => 10.00,
                ],
                [
                    'subtotal' => 20.00,
                    'discount_amount' => -0.00,
                    'shipping_amount' => 5.00,
                    'grand_total' => 25.00,
                ],
                [
                    'subtotal' => 5.00,
                    'discount_amount' => -0.00,
                    'shipping_amount' => 0.00,
                    'grand_total' => 5.00,
                ]
            ],
            'Discount = 1stOrderSubtotal: only 1st order gets discount' => [
                10,
                [
                    'subtotal' => 10.00,
                    'discount_amount' => -10.00,
                    'shipping_amount' => 5.00,
                    'grand_total' => 5.00,
                ],
                [
                    'subtotal' => 20.00,
                    'discount_amount' => -0.00,
                    'shipping_amount' => 5.00,
                    'grand_total' => 25.00,
                ],
                [
                    'subtotal' => 5.00,
                    'discount_amount' => -0.00,
                    'shipping_amount' => 0.00,
                    'grand_total' => 5.00,
                ]
            ],
            'Discount > 1stOrderSubtotal: 1st order get 100% discount and 2nd order get the remaining discount' => [
                15,
                [
                    'subtotal' => 10.00,
                    'discount_amount' => -10.00,
                    'shipping_amount' => 5.00,
                    'grand_total' => 5.00,
                ],
                [
                    'subtotal' => 20.00,
                    'discount_amount' => -5.00,
                    'shipping_amount' => 5.00,
                    'grand_total' => 20.00,
                ],
                [
                    'subtotal' => 5.00,
                    'discount_amount' => -0.00,
                    'shipping_amount' => 0.00,
                    'grand_total' => 5.00,
                ]
            ],
            'Discount = 1stOrderSubtotal + 2ndOrderSubtotal: 1st order and 2nd order get 100% discount' => [
                30,
                [
                    'subtotal' => 10.00,
                    'discount_amount' => -10.00,
                    'shipping_amount' => 5.00,
                    'grand_total' => 5.00,
                ],
                [
                    'subtotal' => 20.00,
                    'discount_amount' => -20.00,
                    'shipping_amount' => 5.00,
                    'grand_total' => 5.00,
                ],
                [
                    'subtotal' => 5.00,
                    'discount_amount' => -0.00,
                    'shipping_amount' => 0.00,
                    'grand_total' => 5.00,
                ]
            ],
            'Discount > 1stOrdSubtotal + 2ndOrdSubtotal: 1st order and 2nd order get 100% discount
             and 3rd order get remaining discount' => [
                31,
                [
                    'subtotal' => 10.00,
                    'discount_amount' => -10.00,
                    'shipping_amount' => 5.00,
                    'grand_total' => 5.00,
                ],
                [
                    'subtotal' => 20.00,
                    'discount_amount' => -20.00,
                    'shipping_amount' => 5.00,
                    'grand_total' => 5.00,
                ],
                [
                    'subtotal' => 5.00,
                    'discount_amount' => -1.00,
                    'shipping_amount' => 0.00,
                    'grand_total' => 4.00,
                ]
            ]
        ];
    }

    /**
     * Get list of orders by quote id.
     *
     * @param int $quoteId
     * @return array
     */
    private function getOrderList(int $quoteId): array
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('quote_id', $quoteId)
            ->create();

        /** @var OrderRepositoryInterface $orderRepository */
        $orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        return $orderRepository->getList($searchCriteria)->getItems();
    }

    /**
     * Get rule by name
     *
     * @param string $name
     * @return Rule
     */
    private function getRule(string $name): Rule
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('name', $name)
            ->create();
        /** @var RuleRepositoryInterface $ruleRepository */
        $ruleRepository = $this->objectManager->get(RuleRepositoryInterface::class);
        $items = $ruleRepository->getList($searchCriteria)
            ->getItems();
        /** @var Rule $salesRule */
        $dataModel = array_pop($items);
        /** @var Rule $ruleModel */
        $ruleModel = $this->objectManager->get(\Magento\SalesRule\Model\RuleFactory::class)->create();
        $ruleModel->load($dataModel->getRuleId());
        return $ruleModel;
    }

    /**
     * Save rule into database
     *
     * @param Rule $rule
     * @return void
     */
    private function saveRule(Rule $rule): void
    {
        /** @var \Magento\SalesRule\Model\ResourceModel\Rule $resourceModel */
        $resourceModel = $this->objectManager->get(\Magento\SalesRule\Model\ResourceModel\Rule::class);
        $resourceModel->save($rule);
    }
}
