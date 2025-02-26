<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OrderCancellationGraphQl\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\OrderCancellation\Model\Email\ConfirmationKeySender;
use Magento\OrderCancellation\Model\GetConfirmationKey;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\SalesGraphQl\Model\Formatter\Order as OrderFormatter;
use Magento\SalesGraphQl\Model\Order\Token;

class CancelOrderGuest
{
    /**
     * CancelOrderGuest Constructor
     *
     * @param OrderFormatter $orderFormatter
     * @param OrderRepositoryInterface $orderRepository
     * @param ConfirmationKeySender $confirmationKeySender
     * @param GetConfirmationKey $confirmationKey
     * @param Uid $idEncoder
     * @param Token $token
     */
    public function __construct(
        private readonly OrderFormatter           $orderFormatter,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ConfirmationKeySender    $confirmationKeySender,
        private readonly GetConfirmationKey       $confirmationKey,
        private readonly Uid                      $idEncoder,
        private readonly Token                    $token
    ) {
    }

    /**
     * Generates and sends a cancellation confirmation key to the guest email
     *
     * @param Order $order
     * @param array $input
     * @return array
     */
    public function execute(Order $order, array $input): array
    {
        try {
            // send confirmation key and order id
            $this->sendConfirmationKeyEmail($order, $input['reason']);

            return [
                'order' => $this->orderFormatter->format($order)
            ];
        } catch (LocalizedException $exception) {
            return [
                'error' => __($exception->getMessage())
            ];
        }
    }

    /**
     * Sends a confirmation key and order id to the guest email which can be used to cancel the guest order
     *
     * @param Order $order
     * @param string $reason
     * @return void
     * @throws LocalizedException
     */
    private function sendConfirmationKeyEmail(Order $order, string $reason): void
    {
        $this->confirmationKeySender->execute(
            $order,
            [
                'order_id' => $this->idEncoder->encode((string)$order->getEntityId()),
                'confirmation_key' => $this->confirmationKey->execute($order, $reason),
                'orderRef' => $this->token->encrypt(
                    $order->getIncrementId(),
                    $order->getBillingAddress()->getEmail(),
                    $order->getBillingAddress()->getLastname()
                ),
                'action' => 'cancel'
            ]
        );

        // add comment in order about confirmation key send
        $order->addCommentToStatusHistory(
            'Order cancellation confirmation key was sent via email.',
            $order->getStatus(),
            true
        );
        $this->orderRepository->save($order);
    }
}
