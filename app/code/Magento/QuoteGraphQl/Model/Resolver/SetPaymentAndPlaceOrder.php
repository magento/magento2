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
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\QuoteGraphQl\Model\Cart\SetPaymentAndPlaceOrder as SetPaymentAndPlaceOrderModel;
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
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var SetPaymentAndPlaceOrderModel
     */
    private $setPaymentAndPlaceOrder;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param GetCartForUser $getCartForUser
     * @param SetPaymentAndPlaceOrderModel $setPaymentAndPlaceOrder
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        SetPaymentAndPlaceOrderModel $setPaymentAndPlaceOrder,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->setPaymentAndPlaceOrder = $setPaymentAndPlaceOrder;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }

        if (empty($args['input']['payment_method']['code'])) {
            throw new GraphQlInputException(__('Required parameter "code" for "payment_method" is missing.'));
        }

        $maskedCartId = $args['input']['cart_id'];
        $paymentData = $args['input']['payment_method'];

        $userId = (int)$context->getUserId();
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

        try {
            $cart = $this->getCartForUser->getCartForCheckout($maskedCartId, $userId, $storeId);
            $orderId = $this->setPaymentAndPlaceOrder->execute($cart, $maskedCartId, $userId, $paymentData);
            $order = $this->orderRepository->get($orderId);
        } catch (GraphQlInputException | GraphQlNoSuchEntityException | GraphQlAuthorizationException $e) {
            throw $e;
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__('Unable to place order: %message', ['message' => $e->getMessage()]), $e);
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
