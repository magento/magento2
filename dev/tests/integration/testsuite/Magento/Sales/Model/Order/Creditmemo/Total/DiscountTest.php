<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order\Creditmemo\Total;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Test\Fixture\Invoice as InvoiceFixture;
use Magento\SalesRule\Model\Rule as SalesRule;
use Magento\SalesRule\Test\Fixture\Rule as RuleFixture;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Test\Fixture\TaxRate as TaxRateFixture;
use Magento\Tax\Test\Fixture\TaxRule as TaxRuleFixture;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Credit memo discount for an order with both a shipping discount and shipping tax
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DiscountTest extends TestCase
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var CreditmemoFactory
     */
    private $creditmemoFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->creditmemoFactory = $objectManager->get(CreditmemoFactory::class);
        $this->orderRepository = $objectManager->get(OrderRepositoryInterface::class);
    }

    /**
     * The credit memo refunds the share of the shipping discount that matches the shipping it refunds
     *
     * The order: 2 x 100 with a 40% cart rule applied to shipping, flat rate shipping 100 per order and 10% tax on
     * products and shipping. Its shipping discount is 40, its shipping tax 6 (on the discounted shipping) and its
     * shipping incl. tax 110 (the shipping plus the tax before the discount).
     *
     * @param float $qty
     * @param float|null $shippingAmount
     * @param array $expected
     * @return void
     */
    #[
        DbIsolation(true),
        ConfigFixture('tax/classes/shipping_tax_class', 2, ScopeInterface::SCOPE_STORE, 'default'),
        ConfigFixture('carriers/flatrate/type', 'O', ScopeInterface::SCOPE_STORE, 'default'),
        ConfigFixture('carriers/flatrate/price', 100, ScopeInterface::SCOPE_STORE, 'default'),
        DataFixture(TaxRateFixture::class, ['tax_country_id' => 'US', 'rate' => 10], 'taxRate'),
        DataFixture(
            TaxRuleFixture::class,
            ['customer_tax_class_ids' => [3], 'product_tax_class_ids' => [2], 'tax_rate_ids' => ['$taxRate.id$']],
            'taxRule'
        ),
        DataFixture(
            RuleFixture::class,
            [
                'website_ids' => [1],
                'customer_group_ids' => [0, 1, 2, 3],
                'simple_action' => SalesRule::BY_PERCENT_ACTION,
                'discount_amount' => 40,
                'apply_to_shipping' => 1,
            ],
            'cartPriceRule'
        ),
        DataFixture(ProductFixture::class, ['price' => 100], 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 2]
        ),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], 'invoice'),
        DataProvider('shippingDiscountDataProvider'),
    ]
    public function testShippingDiscount(float $qty, ?float $shippingAmount, array $expected): void
    {
        /** @var Order $order */
        $order = $this->orderRepository->get($this->fixtures->get('order')->getId());
        $this->assertEqualsWithDelta(100, $order->getBaseShippingAmount(), 0.0001);
        $this->assertEqualsWithDelta(40, $order->getBaseShippingDiscountAmount(), 0.0001);
        $this->assertEqualsWithDelta(6, $order->getBaseShippingTaxAmount(), 0.0001);
        $this->assertEqualsWithDelta(110, $order->getBaseShippingInclTax(), 0.0001);
        $this->assertEqualsWithDelta(198, $order->getBaseGrandTotal(), 0.0001);

        $orderItem = current($order->getAllVisibleItems());
        $data = ['qtys' => [$orderItem->getId() => $qty]];
        if ($shippingAmount !== null) {
            $data['shipping_amount'] = $shippingAmount;
        }
        $creditmemo = $this->creditmemoFactory->createByOrder($order, $data);

        $this->assertEqualsWithDelta($expected['shipping'], $creditmemo->getBaseShippingAmount(), 0.0001);
        $this->assertEqualsWithDelta($expected['discount'], $creditmemo->getBaseDiscountAmount(), 0.0001);
        $this->assertEqualsWithDelta($expected['tax'], $creditmemo->getBaseTaxAmount(), 0.0001);
        $this->assertEqualsWithDelta($expected['grand_total'], $creditmemo->getBaseGrandTotal(), 0.0001);
    }

    /**
     * @return array
     */
    public static function shippingDiscountDataProvider(): array
    {
        return [
            'one item, refund shipping 0' => [
                'qty' => 1,
                'shippingAmount' => 0,
                'expected' => ['shipping' => 0, 'discount' => -40, 'tax' => 6, 'grand_total' => 66],
            ],
            'all items, no shipping amount' => [
                'qty' => 2,
                'shippingAmount' => null,
                'expected' => ['shipping' => 100, 'discount' => -120, 'tax' => 18, 'grand_total' => 198],
            ],
        ];
    }
}
