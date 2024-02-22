<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Rule\Action\Discount;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Api\Data\TotalsInformationInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\TotalsInformationManagement;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Multishipping\Model\Checkout\Type\Multishipping;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Api\GuestCartItemRepositoryInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Api\GuestCartTotalRepositoryInterface;
use Magento\Quote\Api\GuestCouponManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\RuleFactory;
use Magento\SalesRule\Test\Fixture\Rule as RuleFixture;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Magento\SalesRule\Model\Rule\Action\Discount\CartFixed.
 *
 * @magentoAppArea frontend
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartFixedTest extends TestCase
{
    /**
     * @var float
     */
    private const EPSILON = 0.0000000001;

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
     * @var ObjectManagerInterface
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
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
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

        /** @var QuoteIdMask $quoteIdMask */
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
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStatus(Status::STATUS_ENABLED)
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
     * @throws LocalizedException
     */
    public function testMultishipping(
        float $discount,
        array $firstOrderTotals,
        array $secondOrderTotals,
        array $thirdOrderTotals
    ): void {
        $store = $this->objectManager->get(StoreManagerInterface::class)->getStore();
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
            'Discount $5 proportionally spread between products' => [
                5,
                [
                    'subtotal' => 10.00,
                    'discount_amount' => -1.4300,
                    'shipping_amount' => 5.00,
                    'grand_total' => 13.5700,
                ],
                [
                    'subtotal' => 20.00,
                    'discount_amount' => -2.8600,
                    'shipping_amount' => 5.00,
                    'grand_total' => 22.1400,
                ],
                [
                    'subtotal' => 5.00,
                    'discount_amount' => -0.71,
                    'shipping_amount' => 0.00,
                    'grand_total' => 4.2900,
                ]
            ],
            'Discount $30 proportionally spread between products' => [
                30,
                [
                    'subtotal' => 10.00,
                    'discount_amount' => -8.5700,
                    'shipping_amount' => 5.00,
                    'grand_total' => 6.4300,
                ],
                [
                    'subtotal' => 20.00,
                    'discount_amount' => -17.1400,
                    'shipping_amount' => 5.00,
                    'grand_total' => 7.8600,
                ],
                [
                    'subtotal' => 5.00,
                    'discount_amount' => -4.29,
                    'shipping_amount' => 0.00,
                    'grand_total' => 0.7100,
                ]
            ],
            'Discount $50 which is more then all subtotals combined proportionally spread between products' => [
                50,
                [
                    'subtotal' => 10.00,
                    'discount_amount' => -10.0000,
                    'shipping_amount' => 5.00,
                    'grand_total' => 5.0000,
                ],
                [
                    'subtotal' => 20.00,
                    'discount_amount' => -20.0000,
                    'shipping_amount' => 5.00,
                    'grand_total' => 5.0000,
                ],
                [
                    'subtotal' => 5.00,
                    'discount_amount' => -5.00,
                    'shipping_amount' => 0.00,
                    'grand_total' => 0.0000,
                ]
            ],
        ];
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/SalesRule/_files/cart_rule_50_percent_off_no_condition.php
     * @magentoDataFixture Magento/SalesRule/_files/cart_fixed_10_discount.php
     * @magentoDataFixture Magento/Checkout/_files/quote_with_simple_products.php
     * @dataProvider discountByPercentDataProvider
     * @return void
     */
    public function testDiscountsWhenByPercentRuleAppliedFirstAndCartFixedRuleSecond(
        $percentDiscount,
        $expectedDiscounts
    ): void {
        //Update rule discount
        /** @var Rule $rule */
        $rule = $this->getRule('50% off - July 4');
        $rule->setDiscountAmount($percentDiscount);
        $this->saveRule($rule);
        $quote = $this->getQuote('test_quote_with_simple_products');
        $quote->collectTotals();
        $this->quoteRepository->save($quote);
        $this->assertEquals(21.98, $quote->getBaseSubtotal());
        $this->assertEquals($expectedDiscounts['totalDiscount'], $quote->getShippingAddress()->getDiscountAmount());
        $items = $quote->getAllItems();
        $this->assertCount(2, $items);
        $item = array_shift($items);
        $this->assertEquals('simple1', $item->getSku());
        $this->assertEquals(5.99, $item->getPrice());
        $this->assertEqualsWithDelta($expectedDiscounts[$item->getSku()], $item->getDiscountAmount(), self::EPSILON);
        $item = array_shift($items);
        $this->assertEquals('simple2', $item->getSku());
        $this->assertEquals(15.99, $item->getPrice());
        $this->assertEqualsWithDelta($expectedDiscounts[$item->getSku()], $item->getDiscountAmount(), self::EPSILON);
    }

    public function discountByPercentDataProvider()
    {
        return [
            [
                'percentDiscount' => 0,
                'expectedDiscounts' => ['simple1' => 2.73, 'simple2' => 7.27, 'totalDiscount' => -10]
            ],
            [
                'percentDiscount' => 15.5,
                'expectedDiscounts' => ['simple1' => 3.65, 'simple2' => 9.76, 'totalDiscount' => -13.41]
            ],
            [
                'percentDiscount' => 50,
                'expectedDiscounts' => ['simple1' => 5.72, 'simple2' => 15.27, 'totalDiscount' => -20.99]
            ],
            [
                'percentDiscount' => 100,
                'expectedDiscounts' => ['simple1' => 5.99, 'simple2' => 15.99, 'totalDiscount' => -21.98]
            ],
        ];
    }

    /**
     * @magentoConfigFixture current_store sales/minimum_order/tax_including 1
     * @magentoConfigFixture current_store sales/minimum_order/include_discount_amount 1
     * @magentoConfigFixture current_store tax/calculation/price_includes_tax 1
     * @magentoConfigFixture current_store tax/calculation/shipping_includes_tax 1
     * @magentoConfigFixture current_store tax/calculation/discount_tax 1
     * @magentoConfigFixture current_store tax/calculation/apply_after_discount 1
     * @magentoDataFixture Magento/SalesRule/_files/cart_rule_with_coupon_5_off_no_condition.php
     * @magentoDataFixture Magento/Tax/_files/tax_rule_region_1_al.php
     * @magentoDataFixture Magento/Checkout/_files/quote_with_taxable_product_and_customer.php
     */
    public function testCartFixedDiscountPriceIncludeTax()
    {
        $quote = $this->getQuote('test_order_with_taxable_product');
        $quote->setCouponCode('CART_FIXED_DISCOUNT_5');
        $quote->getShippingAddress()
            ->setShippingMethod('flatrate_flatrate')
            ->setCollectShippingRates(true);
        $quote->collectTotals();
        $this->quoteRepository->save($quote);

        $this->assertEquals(0.4, $quote->getShippingAddress()->getTaxAmount());
        $this->assertEquals(5, $quote->getShippingAddress()->getShippingAmount());
        $this->assertEquals(5, $quote->getShippingAddress()->getSubtotalWithDiscount());
        $this->assertEquals(-5, $quote->getShippingAddress()->getDiscountAmount());
    }

    #[
        DataFixture(ProductFixture::class, ['price' => 15], 'p1'),
        DataFixture(ProductFixture::class, ['price' => 10], 'p2'),
        DataFixture(
            RuleFixture::class,
            [
                'simple_action' => Rule::BY_PERCENT_ACTION,
                'discount_amount' => 50,
                'apply_to_shipping' => 1,
                'stop_rules_processing' => 0,
                'sort_order' => 1,
            ]
        ),
        DataFixture(
            RuleFixture::class,
            [
                'simple_action' => Rule::CART_FIXED_ACTION,
                'discount_amount' => 40,
                'apply_to_shipping' => 1,
                'stop_rules_processing' => 0,
                'sort_order' => 2
            ]
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$p1.id$', 'qty' => 2]),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$p2.id$', 'qty' => 2])
    ]
    public function testCarFixedDiscountWithApplyToShippingAmountAfterADiscount(): void
    {
        $cart = DataFixtureStorageManager::getStorage()->get('cart');
        $totals = $this->getTotals((int) $cart->getId());
        $this->assertEquals(0, $totals->getGrandTotal());
        $this->assertEquals(-70, $totals->getDiscountAmount());
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
     * @throws LocalizedException
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
        $ruleModel = $this->objectManager->get(RuleFactory::class)->create();
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
    /**
     * @param int $cartId
     * @return TotalsInterface
     */
    private function getTotals(int $cartId): TotalsInterface
    {
        /** @var Address $address */
        $address = $this->objectManager->get(AddressFactory::class)->create();
        $totalsManagement = $this->objectManager->get(TotalsInformationManagement::class);
        $address->setAddressType(Address::ADDRESS_TYPE_SHIPPING)
            ->setCountryId('US')
            ->setRegionId(12)
            ->setRegion('California')
            ->setPostcode('90230');
        $addressInformation = $this->objectManager->create(
            TotalsInformationInterface::class,
            [
                'data' => [
                    'address' => $address,
                    'shipping_method_code' => 'flatrate',
                    'shipping_carrier_code' => 'flatrate',
                ],
            ]
        );

        return $totalsManagement->calculate($cartId, $addressInformation);
    }
}
