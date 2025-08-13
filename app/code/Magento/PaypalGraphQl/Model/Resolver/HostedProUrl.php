<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;

/**
 * Resolver to pull HostedProUrl payment information
 */
class HostedProUrl implements ResolverInterface
{
    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * @var CollectionFactoryInterface
     */
    private $orderCollectionFactory;

    /**
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param CollectionFactoryInterface $orderCollectionFactory
     */
    public function __construct(
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        CollectionFactoryInterface $orderCollectionFactory
    ) {
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        $customerId = $context->getUserId();
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $maskedCartId = $args['input']['cart_id'] ?? '';
        try {
            $cartId = $this->maskedQuoteIdToQuoteId->execute($maskedCartId);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }

        $order = $this->getOrderFromQuoteId($cartId, $customerId, $storeId);
        $payment = $order->getPayment();
        $paymentAdditionalInformation = $payment->getAdditionalInformation();

        return [
            'secure_form_url' => $paymentAdditionalInformation['secure_form_url']
        ];
    }

    /**
     * Retrieve an order from its corresponding quote id
     *
     * @param int $quoteId
     * @param int $customerId
     * @param int $storeId
     * @return Order
     * @throws GraphQlNoSuchEntityException
     */
    private function getOrderFromQuoteId(int $quoteId, int $customerId, int $storeId): Order
    {
        $orderCollection = $this->orderCollectionFactory->create($customerId ?? null);
        $orderCollection->addFilter(Order::QUOTE_ID, $quoteId);
        $orderCollection->addFilter(Order::STATUS, Order::STATE_PENDING_PAYMENT);
        $orderCollection->addFilter(Order::STORE_ID, $storeId);

        if ($orderCollection->getTotalCount() !== 1) {
            throw new GraphQlNoSuchEntityException(__('Could not find payment information for cart.'));
        }
        /** @var Order $order */
        $order = $orderCollection->getFirstItem();

        return $order;
    }
}
