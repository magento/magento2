<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Resolver;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Payment\Helper\Data as PaymentDataHelper;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Resolver to pull PayflowLink payment information from pending order
 */
class PayflowLinkToken implements ResolverInterface
{
    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var PaymentDataHelper
     */
    private $paymentDataHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param PaymentDataHelper $paymentDataHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        PaymentDataHelper $paymentDataHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->paymentDataHelper = $paymentDataHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $customerId = $context->getUserId();
        $maskedCartId = $args['input']['cart_id'] ?? '';
        try {
            $cartId = $this->maskedQuoteIdToQuoteId->execute($maskedCartId);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }

        $order = $this->getOrderFromQuoteId($cartId, $customerId);
        $payment = $order->getPayment();
        $paymentAdditionalInformation = $payment->getAdditionalInformation();

        return [
            'mode' => $this->getPaymentMode($payment),
            'paypal_url' => $this->getPayflowLinkUrl($payment),
            'secret_token' => $paymentAdditionalInformation['secure_token'],
            'secret_token_id' => $paymentAdditionalInformation['secure_token_id'],
        ];
    }

    /**
     * Retrieve an order from its corresponding quote id
     *
     * @param int $quoteId
     * @param int $customerId
     * @return Order
     * @throws GraphQlNoSuchEntityException
     */
    private function getOrderFromQuoteId(int $quoteId, int $customerId): Order
    {
        $storeId = (int)$this->storeManager->getStore()->getId();
        $this->searchCriteriaBuilder
            ->addFilter(Order::QUOTE_ID, $quoteId, 'eq')
            ->addFilter(Order::STATUS, Order::STATE_PENDING_PAYMENT, 'eq')
            ->addFilter(Order::STORE_ID, $storeId, 'eq');

        if ($customerId) {
            $this->searchCriteriaBuilder->addFilter(Order::CUSTOMER_ID, $customerId, 'eq');
        } else {
            $this->searchCriteriaBuilder->addFilter(Order::CUSTOMER_ID, true, 'null');
        }

        $searchCriteria = $this->searchCriteriaBuilder->create();

        $orderCollection = $this->orderRepository->getList($searchCriteria);
        if ($orderCollection->getTotalCount() !== 1) {
            throw new GraphQlNoSuchEntityException(__('Could not find payment information for cart.'));
        }
        $orders = $orderCollection->getItems();
        $order = reset($orders);

        return $order;
    }

    /**
     * Get payment mode based on sandbox flag
     *
     * @param Payment $payment
     * @return string
     */
    private function getPaymentMode(Payment $payment): string
    {
        $sandboxFlag = $this->paymentDataHelper
            ->getMethodInstance($payment->getMethod())
            ->getConfigData('sandbox_flag');

        return $sandboxFlag ? 'TEST' : 'LIVE';
    }

    /**
     * Get Payflow Link url
     *
     * @param Payment $payment
     * @return string
     */
    private function getPayflowLinkUrl(Payment $payment): string
    {
        $configField = 'cgi_url';
        if ($this->getPaymentMode($payment) === 'TEST') {
            $configField = 'cgi_url_test_mode';
        }

        return $this->paymentDataHelper->getMethodInstance($payment->getMethod())->getConfigData($configField);
    }
}
