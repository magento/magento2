<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\ResourceModel\Order\Handler;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Test\Fixture\Invoice as InvoiceFixture;
use Magento\Sales\Test\Fixture\Shipment as ShipmentFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests order state while an invoice is still open and less than one currency unit is due.
 *
 * @see State
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StateWithOpenInvoiceTest extends TestCase
{
    /**
     * @return void
     */
    #[
        Config('payment/checkmo/active', '1'),
        Config('carriers/flatrate/active', '1'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$', 'email' => 'guest@example.com']),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(
            SetDeliveryMethodFixture::class,
            ['cart_id' => '$cart.id$', 'carrier_code' => 'flatrate', 'method_code' => 'flatrate']
        ),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$', 'method' => 'checkmo']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], 'invoice'),
        DataFixture(ShipmentFixture::class, ['order_id' => '$order.id$'], 'shipment'),
    ]
    public function testOrderStaysProcessingWhileInvoiceWithTotalDueBelowOneIsOpen(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $orderRepository = $objectManager->get(OrderRepositoryInterface::class);

        // Offline payment methods pay the invoice on creation, put it back to the unpaid state
        $invoiceRepository = $objectManager->get(InvoiceRepositoryInterface::class);
        $invoice = $invoiceRepository->get((int)$fixtures->get('invoice')->getEntityId());
        $invoice->setState(Invoice::STATE_OPEN);
        $invoiceRepository->save($invoice);

        $order = $orderRepository->get((int)$fixtures->get('order')->getEntityId());
        $paid = (float)$order->getGrandTotal() - 0.5;
        $order->setTotalPaid($paid)->setBaseTotalPaid($paid)->setState(Order::STATE_PROCESSING);
        $this->assertEquals(0.5, $order->getTotalDue());
        $this->assertFalse($order->canShip());

        $orderRepository->save($order);

        $this->assertSame(Order::STATE_PROCESSING, $orderRepository->get((int)$order->getEntityId())->getState());
    }
}
