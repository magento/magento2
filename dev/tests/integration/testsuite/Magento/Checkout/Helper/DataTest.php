<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Checkout\Helper;

use Magento\Checkout\Helper\Data;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @var QuoteManagement
     */
    private QuoteManagement $quoteManagement;

    /**
     * @var Data
     */
    private Data $checkoutHelper;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var TransportBuilderMock
     */
    private TransportBuilderMock $transportBuilder;

    /**
     * Reserved order ID used in fixture.
     */
    private const FIXTURE_RESERVED_ORDER_ID = 'test_order_with_virtual_product';

    /**
     * Payment method code to use in test.
     */
    private const PAYMENT_METHOD = 'checkmo';

    /**
     * Set up required Magento services for the test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->quoteManagement = $this->objectManager->get(QuoteManagement::class);
        $this->checkoutHelper = $this->objectManager->get(Data::class);
        $this->orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $this->transportBuilder = $this->objectManager->get(TransportBuilderMock::class);
    }

    /**
     * Test sending the "payment failed" email for an order with a virtual product.
     *
     * This test verifies that:
     * - The payment failure email is sent successfully.
     * - The email content does not include shipping address or shipping method
     *   since the product is virtual.
     *
     * @magentoDataFixture Magento/Checkout/_files/quote_with_virtual_product_and_address.php
     *
     * @return void
     */
    public function testSendPaymentFailedEmail(): void
    {
        [$order, $quote] = $this->createOrderFromFixture();
        $this->simulatePaymentFailure($order);

        $this->checkoutHelper->sendPaymentFailedEmail(
            $quote,
            'Simulated payment failure',
            'onepage'
        );

        $message = $this->transportBuilder->getSentMessage();
        $this->assertNotNull($message, 'Expected a payment failed email to be sent.');

        $emailBody = $message->getBody();
        if (method_exists($emailBody, 'bodyToString')) {
            $emailContent = quoted_printable_decode($emailBody->bodyToString());
        } elseif (method_exists($emailBody, 'getParts') && isset($emailBody->getParts()[0])) {
            $emailContent = $emailBody->getParts()[0]->getRawContent();
        } else {
            $this->fail('Unable to extract email content for assertion.');
        }

        $this->assertStringNotContainsString(
            'Shipping Address',
            $emailContent,
            'Shipping address should not appear in the payment failed email for virtual product.'
        );
        $this->assertStringNotContainsString(
            'Shipping Method',
            $emailContent,
            'Shipping method should not appear in the payment failed email for virtual product.'
        );
        $this->assertStringContainsString(
            'Simulated payment failure',
            $emailContent,
            'Expected payment failure message to be present in the email.'
        );
    }

    private function createOrderFromFixture(): array
    {
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class)
            ->load(self::FIXTURE_RESERVED_ORDER_ID, 'reserved_order_id');

        $this->assertNotNull($quote->getId(), 'Failed to load quote from fixture.');
        $this->assertNotEmpty($quote->getAllItems(), 'Quote from fixture is empty.');

        $quote->getPayment()->setMethod(self::PAYMENT_METHOD);

        $order = $this->quoteManagement->submit($quote);

        $this->assertNotNull($order->getId(), 'Order was not created from quote.');
        $this->assertNotEmpty($order->getIncrementId(), 'Order increment ID is missing.');

        return [$order, $quote];
    }

    /**
     * Simulate a payment failure by cancelling the order and adding a history comment.
     *
     * This method updates the order state and status to 'canceled',
     * adds a comment explaining the payment failure, and saves the order.
     *
     * @param Order $order
     * @return void
     */
    private function simulatePaymentFailure(Order $order): void
    {
        $order->setState(Order::STATE_CANCELED)
            ->setStatus(Order::STATE_CANCELED)
            ->addCommentToStatusHistory((string)__('Simulated: Payment failure due to gateway timeout.'));

        $this->orderRepository->save($order);

        $this->assertSame(
            Order::STATE_CANCELED,
            $order->getState(),
            'Order state should be canceled after simulating payment failure.'
        );
    }
}
