<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\GraphQl\Helper\Error\AggregateExceptionMessageFormatter;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\QuoteGraphQl\Model\Cart\PlaceOrder as PlaceOrderModel;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Resolver for placing order after payment method has already been set
 */
class PlaceOrder implements ResolverInterface
{
    /**
     * Static lock prefix for place order locking
     */
    public const LOCK_PREFIX = 'PLACE_ORDER_';

    /**
     * How long to wait for place order to become unlocked
     */
    public const LOCK_TIMEOUT = 60;

    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var PlaceOrderModel
     */
    private $placeOrder;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var AggregateExceptionMessageFormatter
     */
    private $errorMessageFormatter;

    /**
     * @var LockManagerInterface
     */
    private $lockManager;

    /**
     * @param GetCartForUser $getCartForUser
     * @param PlaceOrderModel $placeOrder
     * @param OrderRepositoryInterface $orderRepository
     * @param AggregateExceptionMessageFormatter $errorMessageFormatter
     * @param LockManagerInterface $lockManager
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        PlaceOrderModel $placeOrder,
        OrderRepositoryInterface $orderRepository,
        AggregateExceptionMessageFormatter $errorMessageFormatter,
        LockManagerInterface $lockManager = null
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->placeOrder = $placeOrder;
        $this->orderRepository = $orderRepository;
        $this->errorMessageFormatter = $errorMessageFormatter;
        $this->lockManager = $lockManager ?: ObjectManager::getInstance()->get(LockManagerInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }
        $maskedCartId = $args['input']['cart_id'];
        $userId = (int)$context->getUserId();
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

        $lockedName = self::LOCK_PREFIX . $maskedCartId;
        if ($this->lockManager->isLocked($lockedName)) {
            throw new LocalizedException(__(
                'Unable to place order: A server error stopped your order from being placed. '
                . 'Please try to place your order again'
            ));
        }

        try {
            $this->lockManager->lock($lockedName, self::LOCK_TIMEOUT);
            $cart = $this->getCartForUser->getCartForCheckout($maskedCartId, $userId, $storeId);
            $orderId = $this->placeOrder->execute($cart, $maskedCartId, $userId);
            $order = $this->orderRepository->get($orderId);
        } catch (LocalizedException $e) {
            throw $this->errorMessageFormatter->getFormatted(
                $e,
                __('Unable to place order: A server error stopped your order from being placed. ' .
                    'Please try to place your order again'),
                'Unable to place order',
                $field,
                $context,
                $info
            );
        } finally {
            $this->lockManager->unlock($lockedName);
        }

        return [
            'order' => [
                'order_number' => $order->getIncrementId(),
                // @deprecated The order_id field is deprecated, use order_number instead
                'order_id' => $order->getIncrementId(),
            ],
        ];
    }
}
