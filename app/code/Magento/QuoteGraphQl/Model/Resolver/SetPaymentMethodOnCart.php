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
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Quote\Api\Data\PaymentInterfaceFactory;
use Magento\Quote\Api\PaymentMethodManagementInterface;

/**
 * Mutation resolver for setting payment method for shopping cart
 */
class SetPaymentMethodOnCart implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var PaymentMethodManagementInterface
     */
    private $paymentMethodManagement;

    /**
     * @var PaymentInterfaceFactory
     */
    private $paymentFactory;

    /**
     * @param GetCartForUser $getCartForUser
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     * @param PaymentInterfaceFactory $paymentFactory
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        PaymentMethodManagementInterface $paymentMethodManagement,
        PaymentInterfaceFactory $paymentFactory
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->paymentFactory = $paymentFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($args['input']['cart_id']) || empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing.'));
        }
        $maskedCartId = $args['input']['cart_id'];

        if (!isset($args['input']['payment_method']['code']) || empty($args['input']['payment_method']['code'])) {
            throw new GraphQlInputException(__('Required parameter "code" for "payment_method" is missing.'));
        }
        $paymentMethodCode = $args['input']['payment_method']['code'];

        $poNumber = $args['input']['payment_method']['purchase_order_number'] ?? null;
        $additionalData = $args['input']['payment_method']['additional_data'] ?? [];

        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId());
        $payment = $this->paymentFactory->create([
            'data' => [
                PaymentInterface::KEY_METHOD => $paymentMethodCode,
                PaymentInterface::KEY_PO_NUMBER => $poNumber,
                PaymentInterface::KEY_ADDITIONAL_DATA => $additionalData,
            ]
        ]);

        try {
            $this->paymentMethodManagement->set($cart->getId(), $payment);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }

        return [
            'cart' => [
                'model' => $cart,
            ],
        ];
    }
}
