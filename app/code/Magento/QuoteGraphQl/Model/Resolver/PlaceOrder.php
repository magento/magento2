<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CartManagementInterface;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * @inheritdoc
 */
class PlaceOrder implements ResolverInterface
{
    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param GetCartForUser $getCartForUser
     * @param CartManagementInterface $cartManagement
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        CartManagementInterface $cartManagement,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->cartManagement = $cartManagement;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }
        $maskedCartId = $args['input']['cart_id'];

        $currentUserId = $context->getUserId();
        $cart = $this->getCartForUser->execute($maskedCartId, $currentUserId);

        try {
            $orderId = $this->cartManagement->placeOrder($cart->getId());
            $order = $this->orderRepository->get($orderId);

            // TODO: populate with data
            return [
                'order' => [
                    'order_id' => $order->getIncrementId()
                ]
            ];
        } catch (LocalizedException $exception) {
            throw new GraphQlInputException(
                __('Unable to place order: %message', ['message' => $exception->getMessage()])
            );
        } catch (\Exception $exception) {
            throw new GraphQlInputException(
                __('Unable to place order. An internal error occurred')
            );
        }
    }
}
