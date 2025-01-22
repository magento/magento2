<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OrderCancellationGraphQl\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\OrderCancellation\Model\CancelOrder as CancelOrderAction;
use Magento\OrderCancellation\Model\ResourceModel\SalesOrderConfirmCancel as SalesOrderConfirmCancelResourceModel;
use Magento\Sales\Model\Order;
use Magento\SalesGraphQl\Model\Formatter\Order as OrderFormatter;

/**
 * Class for Guest order cancellation confirmation
 */
class ConfirmCancelOrder
{
    /**
     * ConfirmCancelOrder Constructor
     *
     * @param OrderFormatter $orderFormatter
     * @param CancelOrderAction $cancelOrderAction
     * @param SalesOrderConfirmCancelResourceModel $confirmationKeyResourceModel
     */
    public function __construct(
        private readonly OrderFormatter $orderFormatter,
        private readonly CancelOrderAction $cancelOrderAction,
        private readonly SalesOrderConfirmCancelResourceModel $confirmationKeyResourceModel,
    ) {
    }

    /**
     * Execute order cancellation for guest order
     *
     * @param Order $order
     * @param array $input
     * @return array
     * @throws GraphQlInputException
     * @throws LocalizedException
     */
    public function execute(Order $order, array $input): array
    {
        $confirmationKeyData = $this->loadConfirmationKeyAndValidate($order, $input['confirmation_key']);
        try {
            $updatedOrder = $this->cancelOrderAction->execute(
                $order,
                $confirmationKeyData['reason']
            );
            return [
                'order' => $this->orderFormatter->format($updatedOrder)
            ];
        } catch (LocalizedException $e) {
            return [
                'error' => __($e->getMessage())
            ];
        }
    }

    /**
     * Loads confirmation key factory if exists
     *
     * @param Order $order
     * @param string $confirmationKey
     * @return array
     * @throws GraphQlInputException
     */
    private function loadConfirmationKeyAndValidate(Order $order, string $confirmationKey): array
    {
        $confirmationKeyData = $this->confirmationKeyResourceModel->get((int)$order->getId());

        if (!isset($confirmationKeyData['confirmation_key']) ||
            $confirmationKeyData['confirmation_key'] !== $confirmationKey) {
            throw new GraphQlInputException(
                __('The order cancellation could not be confirmed.')
            );
        }

        return $confirmationKeyData;
    }
}
