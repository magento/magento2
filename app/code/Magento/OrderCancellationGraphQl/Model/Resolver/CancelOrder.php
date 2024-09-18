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

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\OrderCancellation\Model\CancelOrder as CancelOrderAction;
use Magento\OrderCancellation\Model\Config\Config;
use Magento\OrderCancellationGraphQl\Model\CancelOrderGuest;
use Magento\OrderCancellationGraphQl\Model\Validator\ValidateCustomer;
use Magento\OrderCancellationGraphQl\Model\Validator\ValidateOrder;
use Magento\OrderCancellationGraphQl\Model\Validator\ValidateRequest;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\SalesGraphQl\Model\Formatter\Order as OrderFormatter;

/**
 * Cancels an order
 */
class CancelOrder implements ResolverInterface
{
    /**
     * CancelOrder Constructor
     *
     * @param ValidateRequest $validateRequest
     * @param OrderFormatter $orderFormatter
     * @param OrderRepositoryInterface $orderRepository
     * @param CancelOrderAction $cancelOrderAction
     * @param CancelOrderGuest $cancelOrderGuest
     * @param ValidateOrder $validateOrder
     * @param ValidateCustomer $validateCustomer
     * @param Config $config
     */
    public function __construct(
        private readonly ValidateRequest          $validateRequest,
        private readonly OrderFormatter           $orderFormatter,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly CancelOrderAction        $cancelOrderAction,
        private readonly CancelOrderGuest         $cancelOrderGuest,
        private readonly ValidateOrder            $validateOrder,
        private readonly ValidateCustomer         $validateCustomer,
        private readonly Config                   $config
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
    ) {
        $this->validateRequest->execute($args['input'] ?? []);

        try {
            $order = $this->orderRepository->get($args['input']['order_id']);
            if (!$this->isOrderCancellationEnabled($order)) {
                return $this->createErrorResponse('Order cancellation is not enabled for requested store.');
            }

            $errors = $this->validateOrder->execute($order);
            if ($errors) {
                return $errors;
            }

            if ($order->getCustomerIsGuest()) {
                return $this->cancelOrderGuest->execute($order, $args['input']);
            }

            $this->validateCustomer->execute($order, $context);

            $order = $this->cancelOrderAction->execute($order, $args['input']['reason']);

            return [
                'order' => $this->orderFormatter->format($order)
            ];
        } catch (LocalizedException $e) {
            return $this->createErrorResponse($e->getMessage());
        }
    }

    /**
     * Create error response
     *
     * @param string $message
     * @param OrderInterface|null $order
     * @return array
     * @throws LocalizedException
     */
    private function createErrorResponse(string $message, OrderInterface $order = null): array
    {
        $response = ['error' => __($message)];
        if ($order) {
            $response['order'] = $this->orderFormatter->format($order);
        }

        return $response;
    }

    /**
     * Check if order cancellation is enabled in config
     *
     * @param OrderInterface $order
     * @return bool
     */
    private function isOrderCancellationEnabled(OrderInterface $order): bool
    {
        return $this->config->isOrderCancellationEnabledForStore((int)$order->getStoreId());
    }
}
