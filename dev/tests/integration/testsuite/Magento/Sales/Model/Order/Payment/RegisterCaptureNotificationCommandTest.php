<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order\Payment;

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
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Covers registration of a capture notification for an order stuck in the "Payment Review" state.
 *
 * @see \Magento\Sales\Model\Order\Payment\State\RegisterCaptureNotificationCommand
 */
#[
    DbIsolation(true),
    DataFixture(ProductFixture::class, as: 'product'),
    DataFixture(GuestCartFixture::class, as: 'cart'),
    DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
    DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
]
class RegisterCaptureNotificationCommandTest extends TestCase
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->orderRepository = $objectManager->create(OrderRepositoryInterface::class);
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    /**
     * An order left in "Payment Review" (e.g. a PayPal order pending PayPal's own risk review) must move to
     * "Processing" once the capture notification finally arrives, instead of staying stuck forever.
     */
    public function testOrderTransitionsFromPaymentReviewToProcessingOnCaptureNotification(): void
    {
        $orderId = $this->fixtures->get('order')->getId();
        $order = $this->orderRepository->get($orderId);
        $order->setState(Order::STATE_PAYMENT_REVIEW);
        $order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PAYMENT_REVIEW));
        $this->orderRepository->save($order);

        $order = $this->orderRepository->get($orderId);
        $payment = $order->getPayment();
        $payment->setIsTransactionPending(false);
        $payment->setIsFraudDetected(false);
        $payment->registerCaptureNotification((float)$order->getBaseTotalDue());
        $this->orderRepository->save($order);

        $order = $this->orderRepository->get($orderId);
        $this->assertEquals(
            Order::STATE_PROCESSING,
            $order->getState(),
            'Order stuck in "Payment Review" must transition to "Processing" once the capture is confirmed.'
        );
        $this->assertEquals(
            $order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING),
            $order->getStatus()
        );
    }
}
