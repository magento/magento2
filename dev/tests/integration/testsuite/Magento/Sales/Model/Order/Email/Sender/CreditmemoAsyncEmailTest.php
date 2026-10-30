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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\EmailMessageInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Api\CreditmemoManagementInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Test\Fixture\Invoice as InvoiceFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use PHPUnit\Framework\TestCase;

/**
 * Test credit memo creation for offline payment with async email notification.
 *
 */
class CreditmemoAsyncEmailTest extends TestCase
{
    /**
     * @var CreditmemoFactory
     */
    private $creditmemoFactory;

    /**
     * @var CreditmemoManagementInterface
     */
    private $creditmemoManagement;

    /**
     * @var CreditmemoSender
     */
    private $creditmemoSender;

    /**
     * @var TransportBuilderMock
     */
    private $transportBuilder;

    /**
     * @var array
     */
    private $sentEmails = [];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->creditmemoFactory = $objectManager->get(CreditmemoFactory::class);
        $this->creditmemoManagement = $objectManager->get(CreditmemoManagementInterface::class);
        $this->creditmemoSender = $objectManager->get(CreditmemoSender::class);
        $this->transportBuilder = $objectManager->get(TransportBuilderMock::class);
        $this->sentEmails = [];
        // Capture sent emails
        $this->transportBuilder->setOnMessageSentCallback(
            function (EmailMessageInterface $message) {
                $this->sentEmails[] = $message;
            }
        );
    }

    /**
     * Test: Create Credit Memo for Offline Payment Methods with Async Email Notification
     *
     * @return void
     * @throws LocalizedException
     */
    #[
        Config('payment/checkmo/active', '1'),
        Config('carriers/flatrate/active', '1'),
        Config('sales_email/general/async_sending', '1'),
        Config('sales_email/creditmemo/enabled', '1'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$', 'method' => 'checkmo']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], 'invoice'),
    ]
    public function testCreateCreditmemoWithAsyncEmailNotification(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $order = $fixtures->get('order');
        $this->assertNotNull($order->getId(), 'Order should exist');
        $this->assertTrue($order->hasInvoices(), 'Order should have invoice');
        $this->assertEquals('checkmo', $order->getPayment()->getMethod());
        $creditmemo = $this->creditmemoFactory->createByOrder($order);
        $creditmemoRepository = Bootstrap::getObjectManager()->get(CreditmemoRepositoryInterface::class);
        $creditmemoRepository->save($creditmemo);
        $creditmemo->setSendEmail(true);
        $creditmemoRepository->save($creditmemo);
        $this->assertCount(
            0,
            $this->sentEmails,
            'Email should NOT be sent immediately in async mode'
        );
        $this->assertEmpty(
            $creditmemo->getEmailSent(),
            'EmailSent should be empty until async process sends it'
        );
        $creditmemoEmailCron = Bootstrap::getObjectManager()->get('SalesCreditmemoSendEmailsCron');
        $creditmemoEmailCron->execute();
        $this->assertCount(1, $this->sentEmails, 'One refund email should be sent');
        $email = $this->sentEmails[0];
        $this->assertInstanceOf(EmailMessageInterface::class, $email);
        $this->assertStringContainsString(
            'Credit memo',
            $email->getSubject(),
            'Email subject should contain "Credit memo"'
        );
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->sentEmails = [];
    }
}
