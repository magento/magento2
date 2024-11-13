<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 */
declare(strict_types=1);

namespace Magento\OrderCancellationGraphQl\Model\Resolver;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\OrderCancellationGraphQl\Model\ConfirmCancelOrderGuest as ConfirmCancelOrderGuestModel;
use Magento\OrderCancellationGraphQl\Model\Validator\GuestOrder\ValidateOrder;
use Magento\OrderCancellationGraphQl\Model\Validator\GuestOrder\ValidateRequest;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

/**
 * Cancels a guest order on confirmation
 */
class ConfirmCancelOrderGuest implements ResolverInterface
{
    /**
     * ConfirmCancelOrderGuest Constructor
     *
     * @param ValidateRequest $validateRequest
     * @param OrderRepositoryInterface $orderRepository
     * @param ConfirmCancelOrderGuestModel $confirmCancelOrderGuest
     * @param ValidateOrder $validateOrder
     */
    public function __construct(
        private readonly ValidateRequest              $validateRequest,
        private readonly OrderRepositoryInterface     $orderRepository,
        private readonly ConfirmCancelOrderGuestModel $confirmCancelOrderGuest,
        private readonly ValidateOrder                $validateOrder
    ) {
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
    ): array {
        $this->validateRequest->execute($args['input'] ?? []);

        $order = $this->loadOrder((int)$args['input']['order_id']);
        $errors = $this->validateOrder->execute($order);
        if (!empty($errors)) {
            return $errors;
        }

        return $this->confirmCancelOrderGuest->execute($order, $args['input']);
    }

    /**
     * Load order interface from order id
     *
     * @param int $orderId
     * @return Order
     * @throws LocalizedException
     */
    private function loadOrder(int $orderId): Order
    {
        try {
            return $this->orderRepository->get($orderId);
        } catch (Exception $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }
    }
}
