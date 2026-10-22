<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order\Creditmemo;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\Sales\Api\CreditmemoManagementInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Test\Fixture\Invoice as InvoiceFixture;
use Magento\SalesRule\Model\Rule as SalesRule;
use Magento\SalesRule\Test\Fixture\Rule as SalesRuleFixture;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Test\Fixture\ProductTaxClass as ProductTaxClassFixture;
use Magento\Tax\Test\Fixture\TaxRate as TaxRateFixture;
use Magento\Tax\Test\Fixture\TaxRule as TaxRuleFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PartialRefundTaxTest extends TestCase
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
    }

    #[
        Config('tax/calculation/algorithm', Calculation::CALC_ROW_BASE, 'store', 'default'),
        Config('tax/calculation/apply_after_discount', false, 'store', 'default'),
        Config('tax/calculation/discount_tax', true, 'store', 'default'),
        Config('tax/calculation/price_includes_tax', false, 'store', 'default'),
        Config('carriers/flatrate/price', 0, 'store', 'default'),
        DataFixture(ProductTaxClassFixture::class, as: 'product_tax_class'),
        DataFixture(TaxRateFixture::class, ['rate' => 19], as: 'rate'),
        DataFixture(
            TaxRuleFixture::class,
            [
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => ['$product_tax_class.classId$'],
                'tax_rate_ids' => ['$rate.id$']
            ],
            'tax_rule'
        ),
        DataFixture(
            SalesRuleFixture::class,
            [
                'coupon_type' => SalesRule::COUPON_TYPE_NO_COUPON,
                'simple_action' => SalesRule::CART_FIXED_ACTION,
                'discount_amount' => 200,
                'stop_rules_processing' => true
            ],
            as: 'cart_rule'
        ),
        DataFixture(ProductFixture::class, [
            'price' => 100,
            'custom_attributes' => ['tax_class_id' => '$product_tax_class.classId$']
        ], as: 'product'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'quote'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$quote.id$', 'product_id' => '$product.id$', 'qty' => 3]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$quote.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], 'invoice')
    ]
    public function testPartialCreditmemosSplitItemTaxEvenly(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $order = $this->fixtures->get('order');
        $this->assertEquals(57.0, (float)$order->getTaxAmount());

        $creditmemoFactory = $objectManager->get(CreditmemoFactory::class);
        $creditmemoManagement = $objectManager->get(CreditmemoManagementInterface::class);
        $taxAmounts = [];
        for ($i = 0; $i < 3; $i++) {
            /** @var OrderInterface $order */
            $order = $objectManager->create(OrderInterface::class)->load($order->getId());
            $orderItem = current($order->getAllItems());
            $creditmemo = $creditmemoFactory->createByOrder($order, ['qtys' => [$orderItem->getId() => 1]]);
            $creditmemoManagement->refund($creditmemo, true);
            $taxAmounts[] = (float)$creditmemo->getTaxAmount();
        }

        $this->assertEquals([19.0, 19.0, 19.0], $taxAmounts);
    }
}
