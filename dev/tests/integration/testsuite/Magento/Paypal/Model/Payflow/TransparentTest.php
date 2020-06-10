<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\Payflow;

use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\Payflowpro;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TransparentTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var PaymentInformationManagementInterface
     */
    private $management;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->management = $this->objectManager->get(PaymentInformationManagementInterface::class);
    }

    /**
     * Checks a case when order should be placed in "Suspected Fraud" status based on account verification.
     *
     * @magentoDataFixture Magento/Checkout/_files/quote_with_shipping_method.php
     * @magentoConfigFixture current_store payment/payflowpro/active 1
     * @magentoConfigFixture current_store payment/payflowpro/payment_action Authorization
     * @magentoConfigFixture current_store payment/payflowpro/fmf 1
     */
    public function testPlaceOrderSuspectedFraud(): void
    {
        $quote = $this->getQuote('test_order_1');
        $this->addFraudPayment($quote);
        $payment = $quote->getPayment();
        $pnref = $payment->getAdditionalInformation(Payflowpro::PNREF);

        $orderId = (int)$this->management->savePaymentInformationAndPlaceOrder($quote->getId(), $payment);
        self::assertNotEmpty($orderId);

        /** @var OrderRepositoryInterface $orderManagement */
        $orderManagement = $this->objectManager->get(OrderRepositoryInterface::class);
        $order = $orderManagement->get($orderId);

        self::assertEquals(Order::STATUS_FRAUD, $order->getStatus());
        self::assertEquals(Order::STATE_PAYMENT_REVIEW, $order->getState());

        $transactions = $this->getPaymentTransactionList((int) $orderId);
        self::assertCount(1, $transactions, 'Only one transaction should be present.');

        /** @var TransactionInterface $transaction */
        $transaction = array_pop($transactions);
        self::assertEquals(
            $pnref,
            $transaction->getTxnId(),
            'Authorization transaction id should be equal to PNREF.'
        );

        self::assertStringContainsString(
            'Order is suspended as an account verification transaction is suspected to be fraudulent.',
            $this->getOrderComment($orderId)
        );
    }

    /**
     * Retrieves quote by provided order ID.
     *
     * @param string $reservedOrderId
     * @return CartInterface
     */
    private function getQuote(string $reservedOrderId): CartInterface
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', $reservedOrderId)
            ->create();

        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $items = $quoteRepository->getList($searchCriteria)
            ->getItems();

        return array_pop($items);
    }

    /**
     * Sets payment with fraud to quote.
     *
     * @return void
     */
    private function addFraudPayment(CartInterface $quote): void
    {
        $payment = $quote->getPayment();
        $payment->setMethod(Config::METHOD_PAYFLOWPRO);
        $payment->setAdditionalInformation(Payflowpro::PNREF, 'A90A0D1B361D');
        $payment->setAdditionalInformation('result_code', Payflowpro::RESPONSE_CODE_FRAUDSERVICE_FILTER);
        $payment->setCcType('VI');
        $payment->setCcLast4('1111');
        $payment->setCcExpMonth('3');
        $payment->setCcExpYear('2025');

        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $quoteRepository->save($quote);
    }

    /**
     * Get list of order transactions.
     *
     * @param int $orderId
     * @return TransactionInterface[]
     */
    private function getPaymentTransactionList(int $orderId): array
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('order_id', $orderId)
            ->create();

        /** @var TransactionRepositoryInterface $transactionRepository */
        $transactionRepository = $this->objectManager->get(TransactionRepositoryInterface::class);
        return $transactionRepository->getList($searchCriteria)
            ->getItems();
    }

    /**
     * Returns order comment.
     *
     * @param int $orderId
     * @return string
     */
    private function getOrderComment(int $orderId): string
    {
        /** @var OrderManagementInterface $orderManagement */
        $orderManagement = $this->objectManager->get(OrderManagementInterface::class);
        $comments = $orderManagement->getCommentsList($orderId)->getItems();
        $comment = reset($comments);

        return $comment ? $comment->getComment() : '';
    }
}
