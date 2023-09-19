<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OrderCancellationGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\OrderCancellation\Model\CancelOrder as CancelOrderAction;
use Magento\OrderCancellation\Model\Config\Config;
use Magento\OrderCancellationGraphQl\Model\ValidateRequest;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\SalesGraphQl\Model\Formatter\Order as OrderFormatter;

/**
 * Cancels an order
 */
class CancelOrder implements ResolverInterface
{
    /**
     * @var ValidateRequest $validateRequest
     */
    private ValidateRequest $validateRequest;

    /**
     * @var CancelOrderAction $cancelOrderAction
     */
    private CancelOrderAction $cancelOrderAction;

    /**
     * @var OrderFormatter
     */
    private OrderFormatter $orderFormatter;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @param ValidateRequest $validateRequest
     * @param OrderFormatter $orderFormatter
     * @param OrderRepositoryInterface $orderRepository
     * @param Config $config
     * @param CancelOrderAction $cancelOrderAction
     */
    public function __construct(
        ValidateRequest $validateRequest,
        OrderFormatter $orderFormatter,
        OrderRepositoryInterface $orderRepository,
        Config $config,
        CancelOrderAction $cancelOrderAction
    ) {
        $this->validateRequest = $validateRequest;
        $this->orderFormatter = $orderFormatter;
        $this->orderRepository = $orderRepository;
        $this->config = $config;
        $this->cancelOrderAction = $cancelOrderAction;
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
        $this->validateRequest->execute($context, $args['input'] ?? []);

        try {
            /** @var Order $order */
            $order = $this->orderRepository->get($args['input']['order_id']);

            if ((int) $order->getCustomerId() !== $context->getUserId()) {
                return [
                    'error' => __('Current user is not authorized to cancel this order')
                ];
            }

            if ($order->getState() === order::STATE_CLOSED
                || $order->getState() === order::STATE_CANCELED
                || $order->getState() === order::STATE_HOLDED
                || $order->getState() === order::STATE_COMPLETE
            ) {
                return [
                    'error' => __('Order already closed, complete, cancelled or on hold'),
                    'order' => $this->orderFormatter->format($order)
                ];
            }

            if ($order->hasShipments()) {
                return [
                    'error' => __('Order with one or more items shipped cannot be cancelled'),
                    'order' => $this->orderFormatter->format($order)
                ];
            }

            if (!$this->config->isOrderCancellationEnabledForStore((int)$order->getStoreId())) {
                return [
                    'error' =>  __('Order cancellation is not enabled for requested store.')
                ];
            }

            $order = $this->cancelOrderAction->execute($order, $args['input']['reason']);

            return [
                'order' => $this->orderFormatter->format($order)
            ];
        } catch (LocalizedException $e) {
            return [
                'error' => __($e->getMessage())
            ];
        }
    }
}
