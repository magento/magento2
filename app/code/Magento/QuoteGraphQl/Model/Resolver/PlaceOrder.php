<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
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
        if (!isset($args['input']['cart_id']) || empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }
        $maskedCartId = $args['input']['cart_id'];

        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId());

        if ($context->getUserId() === 0) {
            if (!$cart->getCustomerEmail()) {
                throw new GraphQlInputException(__("Guest email for cart is missing. Please enter"));
            }
            $cart->setCheckoutMethod(CartManagementInterface::METHOD_GUEST);
        }

        try {
            $orderId = $this->cartManagement->placeOrder($cart->getId());
            $order = $this->orderRepository->get($orderId);

            return [
                'order' => [
                    'order_id' => $order->getIncrementId(),
                ],
            ];
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__('Unable to place order: %message', ['message' => $e->getMessage()]), $e);
        }
    }
}
