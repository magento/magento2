<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Sales\Model;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for asynchronous grid processing with auto-invoiced orders
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AsyncGridWithAutoInvoiceTest extends TestCase
{
    /**
     * @var GridAsyncInsert
     */
    private GridAsyncInsert $gridAsyncInsert;

    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $this->fixtures = DataFixtureStorageManager::getStorage();

        /** @var GridAsyncInsert $gridAsyncInsert */
        $this->gridAsyncInsert = $objectManager->get('SalesOrderIndexGridAsyncInsert');
    }

    /**
     * Test async grid processing with auto-invoiced zero subtotal order
     * Expected: Order placed successfully with no errors in logs
     *
     * @return void
     */
    #[
        Config('dev/grid/async_indexing', '1', 'default'),
        Config('carriers/freeshipping/active', '1', 'store', 'default'),
        Config('payment/free/active', '1', 'store', 'default'),
        Config('payment/free/order_status', 'processing', 'store', 'default'),
        Config('payment/free/payment_action', 'authorize_capture', 'store', 'default'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'free-product-test',
                'price' => 0.00,
                'special_price' => null,
            ],
            'product'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$product.id$',
                'qty' => 1
            ]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(
            SetDeliveryMethodFixture::class,
            [
                'cart_id' => '$cart.id$',
                'carrier_code' => 'freeshipping',
                'method_code' => 'freeshipping'
            ]
        ),
        DataFixture(
            SetPaymentMethodFixture::class,
            [
                'cart_id' => '$cart.id$',
                'method' => 'free'
            ]
        ),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order')
    ]
    public function testAsyncGridProcessingWithAutoInvoicedZeroSubtotalOrder(): void
    {
        /** @var OrderInterface $order */
        $order = $this->fixtures->get('order');

        $this->assertNotNull($order->getEntityId(), 'Order should be created successfully');
        $this->assertEquals(0.00, (float) $order->getGrandTotal(), 'Order grand total should be 0.00');
        $this->assertEquals('free', $order->getPayment()->getMethod(), 'Payment method should be "free"');
        $this->assertEquals(
            'freeshipping_freeshipping',
            $order->getShippingMethod(),
            'Shipping method should be "freeshipping_freeshipping"'
        );

        $this->assertEquals(
            Order::STATE_PROCESSING,
            $order->getState(),
            'Order state should be Processing (triggers auto-invoice)'
        );

        $invoiceCollection = $order->getInvoiceCollection();
        $this->assertGreaterThan(
            0,
            $invoiceCollection->count(),
            'Invoice should be automatically created when order status = Processing'
        );

        $exceptionThrown = false;
        $exceptionMessage = '';

        try {
            $this->gridAsyncInsert->asyncInsert();
        } catch (\Exception $e) {
            $exceptionThrown = true;
            $exceptionMessage = $e->getMessage();
        }

        $this->assertFalse(
            $exceptionThrown,
            'There should be no error in the logs. Exception thrown: ' . $exceptionMessage
        );
    }
}
