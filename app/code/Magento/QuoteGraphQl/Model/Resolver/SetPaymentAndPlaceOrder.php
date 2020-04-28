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
use Magento\QuoteGraphQl\Model\Cart\SetPaymentMethodOnCart;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Resolver for setting payment method and placing order
 *
 * @deprecated 100.3.4 Should use setPaymentMethodOnCart and placeOrder mutations in single request.
 * @see \Magento\QuoteGraphQl\Model\Resolver\SetPaymentMethodOnCart
 * @see \Magento\QuoteGraphQl\Model\Resolver\PlaceOrder
 */
class SetPaymentAndPlaceOrder implements ResolverInterface
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
     * @var SetPaymentMethodOnCart
     */
    private $setPaymentMethodOnCart;

    /**
     * @param GetCartForUser $getCartForUser
     * @param CartManagementInterface $cartManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param SetPaymentMethodOnCart $setPaymentMethodOnCart
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        CartManagementInterface $cartManagement,
        OrderRepositoryInterface $orderRepository,
        SetPaymentMethodOnCart $setPaymentMethodOnCart
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->cartManagement = $cartManagement;
        $this->orderRepository = $orderRepository;
        $this->setPaymentMethodOnCart = $setPaymentMethodOnCart;
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

        if (!isset($args['input']['payment_method']['code']) || empty($args['input']['payment_method']['code'])) {
            throw new GraphQlInputException(__('Required parameter "code" for "payment_method" is missing.'));
        }
        $paymentData = $args['input']['payment_method'];

        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);

        if ((int)$context->getUserId() === 0) {
            if (!$cart->getCustomerEmail()) {
                throw new GraphQlInputException(__("Guest email for cart is missing."));
            }
            $cart->setCheckoutMethod(CartManagementInterface::METHOD_GUEST);
        }

        $this->setPaymentMethodOnCart->execute($cart, $paymentData);

        try {
            $orderId = $this->cartManagement->placeOrder($cart->getId());
            $order = $this->orderRepository->get($orderId);

            return [
                'order' => [
                    'order_number' => $order->getIncrementId(),
                    // @deprecated The order_id field is deprecated, use order_number instead
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
