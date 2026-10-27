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
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\EmailMessageInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use PHPUnit\Framework\TestCase;

/**
 * Test order creation from admin scope with async email notification.
 *
 */
class AdminOrderAsyncEmailTest extends TestCase
{
    /**
     * Customer email address for async order notification testing
     */
    private const CUSTOMER_EMAIL = 'async-customer@example.com';

    /**
     * @var TransportBuilderMock
     */
    private TransportBuilderMock $transportBuilder;

    /** @var EmailMessageInterface[] */
    private array $sentEmails = [];

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
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
     * Verifies that an order email is dispatched only after the async cron job runs.
     * Uses a registered customer with accessible email as per test preconditions.
     *
     * @return void
     * @throws LocalizedException
     */
    #[
        Config('payment/checkmo/active', '1'),
        Config('carriers/flatrate/active', '1'),
        Config('sales_email/general/async_sending', '1'),
        Config('sales_email/order/enabled', '1'),
        Config('sales_email/general/sending_limit', '10'),
        DataFixture(CustomerFixture::class, ['email' => 'async-customer@example.com'], as: 'customer'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$', 'method' => 'checkmo']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
    ]
    public function testAsynchronousOrderEmailDispatchedByCron(): void
    {
        Bootstrap::getInstance()->loadArea(Area::AREA_ADMINHTML);

        $fixtures = DataFixtureStorageManager::getStorage();
        $order = $fixtures->get('order');

        $objectManager = Bootstrap::getObjectManager();
        /** @var OrderSender $orderSender */
        $orderSender = $objectManager->get(OrderSender::class);

        $result = $orderSender->send($order);
        $this->assertFalse(
            $result,
            'OrderSender::send must defer sending when async mode is active.'
        );
        $this->assertCount(
            0,
            $this->sentEmails,
            'Email must not be sent immediately in async mode.'
        );
        $this->assertNull(
            $order->getEmailSent(),
            'EmailSent flag should remain null until cron processes the queue.'
        );
        $this->assertTrue(
            (bool)$order->getSendEmail(),
            'SendEmail flag should be recorded for cron processing.'
        );

        $cron = $objectManager->get('SalesOrderSendEmailsCron');
        $cron->execute();
        $cron->execute();

        $this->assertCount(
            1,
            $this->sentEmails,
            'Exactly one order confirmation email should be sent by the cron.'
        );
        $email = $this->sentEmails[0];
        $this->assertInstanceOf(EmailMessageInterface::class, $email);
        $this->assertStringContainsString(
            'order confirmation',
            $email->getSubject(),
            'Order confirmation subject should contain the word "Order".'
        );
        $this->assertEquals(
            self::CUSTOMER_EMAIL,
            $email->getTo()[0]->getEmail(),
            'Email should be addressed to the customer used during checkout.'
        );
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->transportBuilder->clean();
        $this->sentEmails = [];
    }
}
