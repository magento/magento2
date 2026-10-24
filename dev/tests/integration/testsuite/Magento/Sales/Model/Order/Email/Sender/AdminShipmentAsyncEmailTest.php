<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order\Email\Sender;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\EmailMessageInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Shipping\Model\ShipmentNotifier;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use PHPUnit\Framework\TestCase;

/**
 * Test shipment creation with offline payment method and async email notification.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdminShipmentAsyncEmailTest extends TestCase
{
    /**
     * @var TransportBuilderMock
     */
    private TransportBuilderMock $transportBuilder;

    /**
     * @var EmailMessageInterface[]
     */
    private array $sentEmails = [];

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = Bootstrap::getObjectManager();
        $this->transportBuilder = $objectManager->get(TransportBuilderMock::class);
        $this->sentEmails = [];

        $this->transportBuilder->setOnMessageSentCallback(
            function (EmailMessageInterface $message): void {
                $this->sentEmails[] = $message;
            }
        );
    }

    /**
     * Tests shipment email async behavior: not sent immediately, then sent by cron.
     *
     * @return void
     * @throws LocalizedException
     * @throws MailException
     */
    #[
        Config('payment/checkmo/active', '1'),
        Config('carriers/flatrate/active', '1'),
        Config('sales_email/general/async_sending', '1'),
        Config('sales_email/shipment/enabled', '1'),
        Config('sales_email/general/sending_limit', '10'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$', 'email' => 'async-shipment@example.com']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$', 'method' => 'checkmo']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
    ]
    public function testShipmentAsyncEmailBehavior(): void
    {
        Bootstrap::getInstance()->loadArea(Area::AREA_ADMINHTML);
        $shipment = $this->createShipmentForOrder();
        $objectManager = Bootstrap::getObjectManager();
        $shipmentNotifier = $objectManager->get(ShipmentNotifier::class);
        $notifyResult = $shipmentNotifier->notify($shipment);
        $this->assertFalse(
            $notifyResult,
            'ShipmentNotifier::notify should defer sending when async mode is active.'
        );
        $this->assertCount(
            0,
            $this->sentEmails,
            'Email must not be sent immediately in async mode.'
        );
        $cron = $objectManager->get('SalesShipmentSendEmailsCron');
        $cron->execute();
        $this->assertCount(
            1,
            $this->sentEmails,
            'One shipment email should be dispatched after cron execution.'
        );
        $this->assertShipmentEmailContent($this->sentEmails[0]);
    }

    /**
     * Creates an invoice for the given order.
     *
     * @param Order $order
     * @return void
     * @throws LocalizedException
     */
    private function createInvoiceForOrder(Order $order): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $invoiceService = $objectManager->get(InvoiceService::class);
        $invoiceRepository = $objectManager->get(InvoiceRepositoryInterface::class);
        $orderRepository = $objectManager->get(OrderRepositoryInterface::class);
        $invoice = $invoiceService->prepareInvoice($order);
        $invoice->register();
        $invoice->setSendEmail(false);
        $invoiceRepository->save($invoice);
        $orderRepository->save($order);
    }

    /**
     * Gets order from fixture storage.
     *
     * @return Order
     */
    private function getOrderFromFixture(): Order
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        /** @var Order $fixtureOrder */
        $fixtureOrder = $fixtures->get('order');
        $objectManager = Bootstrap::getObjectManager();
        $orderRepository = $objectManager->get(OrderRepositoryInterface::class);
        return $orderRepository->get((int)$fixtureOrder->getEntityId());
    }

    /**
     * Creates a shipment for the order from fixtures.
     *
     * @return ShipmentInterface
     * @throws LocalizedException
     */
    private function createShipmentForOrder(): ShipmentInterface
    {
        $order = $this->getOrderFromFixture();
        $objectManager = Bootstrap::getObjectManager();
        $shipmentRepository = $objectManager->get(ShipmentRepositoryInterface::class);
        $shipmentFactory = $objectManager->get(ShipmentFactory::class);
        $orderRepository = $objectManager->get(OrderRepositoryInterface::class);
        $this->createInvoiceForOrder($order);
        $quantities = $this->calculateShippableQuantities($order);
        $shipment = $shipmentFactory->create($order, $quantities);
        $shipment->register();
        $shipment->setSendEmail(true);
        $shipmentRepository->save($shipment);
        $orderRepository->save($shipment->getOrder());

        return $shipment;
    }

    /**
     * Calculates shippable quantities for order items.
     *
     * @param Order $order
     * @return array
     */
    private function calculateShippableQuantities(Order $order): array
    {
        $quantities = [];
        foreach ($order->getAllItems() as $orderItem) {
            if ($orderItem->getIsVirtual()) {
                continue;
            }
            $qtyToShip = $orderItem->getQtyOrdered() - $orderItem->getQtyShipped();
            if ($qtyToShip > 0) {
                $quantities[$orderItem->getItemId()] = $qtyToShip;
            }
        }
        return $quantities;
    }

    /**
     * Asserts shipment email has correct content.
     *
     * @param EmailMessageInterface $email
     * @return void
     */
    private function assertShipmentEmailContent(EmailMessageInterface $email): void
    {
        $this->assertInstanceOf(EmailMessageInterface::class, $email);
        $this->assertStringContainsString(
            'order has shipped',
            $email->getSubject(),
            'Email subject should contain shipment confirmation text.'
        );

        // Assert getTo() returns a non-empty array
        $recipients = $email->getTo();
        $this->assertNotEmpty(
            $recipients,
            'Email should have at least one recipient.'
        );
        $this->assertIsArray(
            $recipients,
            'Email recipients should be returned as an array.'
        );

        // Now safely access the first recipient
        $this->assertEquals(
            'async-shipment@example.com',
            $recipients[0]->getEmail(),
            'Email should be sent to the customer email address.'
        );
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->transportBuilder->clean();
        $this->sentEmails = [];
        parent::tearDown();
    }
}
