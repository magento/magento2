<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\SalesRule\Model\ResourceModel\Report\Rule;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Test\Fixture\ApplyCoupon as ApplyCouponFixture;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Sales\Model\Order;
use Magento\Sales\Test\Fixture\Invoice as InvoiceFixture;
use Magento\Sales\Test\Fixture\Shipment as ShipmentFixture;
use Magento\SalesRule\Model\ResourceModel\Report\Collection;
use Magento\SalesRule\Model\ResourceModel\Report\Rule;
use Magento\SalesRule\Model\Rule as SalesRule;
use Magento\SalesRule\Test\Fixture\AddressCondition as AddressConditionFixture;
use Magento\SalesRule\Test\Fixture\Rule as RuleFixture;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Test\Fixture\TaxRate as TaxRateFixture;
use Magento\Tax\Test\Fixture\TaxRule as TaxRuleFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Createdat test for check report totals calculate
 */
class CreatedatTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var Rule
     */
    private $reportResource;

    /**
     * @var Collection
     */
    private $reportCollection;

    /**
     * @inheirtDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->reportResource = $this->objectManager->get(Rule::class);
        $this->reportCollection = $this->objectManager->get(Collection::class);
    }

    /**
     * @magentoDataFixture Magento/SalesRule/_files/order_with_coupon.php
     * @dataProvider orderParamsDataProvider()
     * @param $orderParams
     */
    public function testTotals($orderParams)
    {
        /** @var Order $order */
        $order = $this->objectManager->create(Order::class);
        $order->loadByIncrementId('100000001')
            ->setBaseGrandTotal($orderParams['base_subtotal'])
            ->setSubtotal($orderParams['base_subtotal'])
            ->setBaseSubtotal($orderParams['base_subtotal'])
            ->setBaseDiscountAmount($orderParams['base_discount_amount'])
            ->setBaseTaxAmount($orderParams['base_tax_amount'])
            ->setBaseSubtotalInvoiced($orderParams['base_subtotal_invoiced'])
            ->setBaseDiscountInvoiced($orderParams['base_discount_invoiced'])
            ->setBaseTaxInvoiced($orderParams['base_tax_invoiced'])
            ->setBaseShippingAmount(0)
            ->setBaseToGlobalRate(1)
            ->setCouponCode('1234567890')
            ->setCreatedAt('2014-10-25 10:10:10')
            ->save();
        // refresh report statistics
        $this->reportResource->aggregate();
        $salesRuleReportItem = $this->reportCollection->getFirstItem();
        $this->assertEquals($this->getTotalAmount($order), $salesRuleReportItem['total_amount']);
        $this->assertEquals($this->getTotalAmountActual($order), $salesRuleReportItem['total_amount_actual']);
    }

    /**
     * Repeat sql formula from \Magento\SalesRule\Model\ResourceModel\Report\Rule\Createdat::_aggregateByOrder
     *
     * @param Order $order
     * @return float
     */
    private function getTotalAmount(Order $order)
    {
        return (
                ($order->getBaseSubtotal() - $order->getBaseSubtotalCanceled()
                    + ($order->getBaseShippingAmount() - $order->getBaseShippingCanceled()))
                - (abs((float) $order->getBaseDiscountAmount()) - abs((float) $order->getBaseDiscountCanceled()))
                + ($order->getBaseTaxAmount() - $order->getBaseTaxCanceled())
                + ($order->getBaseDiscountTaxCompensationAmount() - $order->getBaseDiscountTaxCompensationRefunded())
                - abs((float) $order->getShippingDiscountTaxCompensationAmount())
            ) * $order->getBaseToGlobalRate();
    }

    /**
     * Repeat sql formula from \Magento\SalesRule\Model\ResourceModel\Report\Rule\Createdat::_aggregateByOrder
     *
     * @param Order $order
     * @return float
     */
    private function getTotalAmountActual(Order $order)
    {
        return (
                ($order->getBaseSubtotalInvoiced() - $order->getSubtotalRefunded()
                    + ($order->getBaseShippingInvoiced() - $order->getBaseShippingRefunded()))
                - abs((float) $order->getBaseDiscountInvoiced()) - abs((float) $order->getBaseDiscountRefunded())
                + $order->getBaseTaxInvoiced() - $order->getBaseTaxRefunded()
                + ($order->getBaseDiscountTaxCompensationInvoiced() - $order->getBaseDiscountTaxCompensationRefunded())
                - abs((float) $order->getBaseShippingDiscountTaxCompensationAmnt())
            ) * $order->getBaseToGlobalRate();
    }

    /**
     * @return array
     */
    public static function orderParamsDataProvider()
    {
        return [
            [
                [
                    'base_discount_amount' => 98.80,
                    'base_subtotal' => 494,
                    'base_tax_amount' => 8.8,
                    'base_subtotal_invoiced' => 494,
                    'base_discount_invoiced' => 98.80,
                    'base_tax_invoiced' => 8.8
                ]
            ]
        ];
    }

    #[
        DbIsolation(false),
        AppIsolation(true),
        AppArea('adminhtml'),
        ConfigFixture('tax/classes/shipping_tax_class', 2, ScopeInterface::SCOPE_STORE),
        ConfigFixture('tax/classes/wrapping_tax_class', 0, ScopeInterface::SCOPE_STORE),

        ConfigFixture('tax/calculation/algorithm', 'TOTAL_BASE_CALCULATION', ScopeInterface::SCOPE_STORE),
        ConfigFixture('tax/calculation/based_on', 'shipping', ScopeInterface::SCOPE_STORE),
        ConfigFixture('tax/calculation/price_includes_tax', 1, ScopeInterface::SCOPE_STORE),
        ConfigFixture('tax/calculation/shipping_includes_tax', 1, ScopeInterface::SCOPE_STORE),
        ConfigFixture('tax/calculation/apply_after_discount', 1, ScopeInterface::SCOPE_STORE),
        ConfigFixture('tax/calculation/discount_tax', 1, ScopeInterface::SCOPE_STORE),
        ConfigFixture('tax/calculation/apply_tax_on', 0, ScopeInterface::SCOPE_STORE),
        ConfigFixture('tax/calculation/cross_border_trade_enabled', 1, ScopeInterface::SCOPE_STORE),

        ConfigFixture('tax/notification/ignore_discount', 0, ScopeInterface::SCOPE_STORE),
        ConfigFixture('tax/notification/ignore_price_display', 0, ScopeInterface::SCOPE_STORE),
        ConfigFixture('tax/notification/ignore_apply_discount', 0, ScopeInterface::SCOPE_STORE),

        ConfigFixture('tax/display/type', 2, ScopeInterface::SCOPE_STORE),
        ConfigFixture('tax/display/shipping', 2, ScopeInterface::SCOPE_STORE),

        ConfigFixture('tax/cart_display/price', 2, ScopeInterface::SCOPE_STORE),
        ConfigFixture('tax/cart_display/subtotal', 2, ScopeInterface::SCOPE_STORE),
        ConfigFixture('tax/cart_display/shipping', 2, ScopeInterface::SCOPE_STORE),
        ConfigFixture('tax/cart_display/full_summary', 1, ScopeInterface::SCOPE_STORE),
        ConfigFixture('tax/cart_display/zero_tax', 1, ScopeInterface::SCOPE_STORE),

        ConfigFixture('tax/sales_display/price', 2, ScopeInterface::SCOPE_STORE),
        ConfigFixture('tax/sales_display/subtotal', 2, ScopeInterface::SCOPE_STORE),
        ConfigFixture('tax/sales_display/shipping', 2, ScopeInterface::SCOPE_STORE),
        ConfigFixture('tax/sales_display/full_summary', 1, ScopeInterface::SCOPE_STORE),
        ConfigFixture('tax/sales_display/zero_tax', 1, ScopeInterface::SCOPE_STORE),

        DataFixture(
            TaxRateFixture::class,
            [
                'code' => 'US 19%',
                'tax_country_id' => 'USA',
                'rate' => 19,
            ],
            'taxRate'
        ),
        DataFixture(
            TaxRuleFixture::class,
            [
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => [2],
                'tax_rate_ids' => ['$taxRate.id$']
            ],
            'taxRule'
        ),
        DataFixture(
            AddressConditionFixture::class,
            ['attribute' => 'base_subtotal_total_incl_tax', 'operator' => '>=', 'value' => 12],
            'condition'
        ),
        DataFixture(
            RuleFixture::class,
            [
                'website_ids' => [1],
                'customer_group_ids' => [0, 1, 2, 3],
                'coupon_code' => 'COUPON1',
                'coupon_type' => SalesRule::COUPON_TYPE_SPECIFIC,
                'simple_action' => SalesRule::BY_PERCENT_ACTION,
                'uses_per_customer' => 10,
                'discount_amount' => 10,
                'stop_rules_processing' => true,
                'conditions' => ['$condition$']
            ],
            'cartPriceRule'
        ),
        DataFixture(
            Customer::class,
            [
                'email' => 'customer@example.com',
                'password' => 'password'
            ],
            'customer'
        ),
        DataFixture(ProductFixture::class, ['price' => 17000, 'qty' => 1, 'special_price' => 16730], as:'product'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$product.id$',
                'qty' => 1
            ]
        ),
        DataFixture(ApplyCouponFixture::class, ['cart_id' => '$cart.id$', 'coupon_codes' => ['COUPON1']]),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], 'invoice'),
        DataFixture(ShipmentFixture::class, ['order_id' => '$order.id$'], 'shipment')
    ]
    public function testCouponsReport(): void
    {
        $order = $this->fixtures->get('order');
        $this->reportResource->aggregate();
        $salesRuleReportItem = $this->reportCollection->getFirstItem();

        $this->assertEquals(
            round($order->getData('subtotal_incl_tax'), 2),
            round($salesRuleReportItem['subtotal_amount'], 2)
        );
        $this->assertEquals(
            round($order->getData('base_subtotal_incl_tax'), 2),
            round($salesRuleReportItem['subtotal_amount_actual'], 2)
        );

        $this->assertEquals(
            round($this->getTotalAmount($order), 2),
            round($salesRuleReportItem['total_amount'], 2)
        );
        $this->assertEquals(
            round($this->getTotalAmountActual($order), 2),
            round($salesRuleReportItem['total_amount_actual'], 2)
        );
    }
}
