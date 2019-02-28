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
use Magento\Framework\Stdlib\ArrayManager;
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
     * @var ArrayManager
     */
    private $arrayManager;

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
     * @param ArrayManager $arrayManager
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     * @param PaymentInterfaceFactory $paymentFactory
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        ArrayManager $arrayManager,
        PaymentMethodManagementInterface $paymentMethodManagement,
        PaymentInterfaceFactory $paymentFactory
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->arrayManager = $arrayManager;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->paymentFactory = $paymentFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $maskedCartId = (string)$this->arrayManager->get('input/cart_id', $args);
        if (!$maskedCartId) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }

        $paymentMethod = $this->arrayManager->get('input/payment_method', $args);
        if (!$paymentMethod) {
            throw new GraphQlInputException(__('Required parameter "payment_method" is missing'));
        }

        $paymentMethodCode = (string) $this->arrayManager->get('input/payment_method/code', $args);
        if (!$paymentMethodCode) {
            throw new GraphQlInputException(__('Required parameter payment "code" is missing'));
        }

        $poNumber = $this->arrayManager->get('input/payment_method/purchase_order_number', $args);

        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId());
        $payment = $this->paymentFactory->create([
            'data' => [
                PaymentInterface::KEY_METHOD => $paymentMethodCode,
                PaymentInterface::KEY_PO_NUMBER => $poNumber,
                PaymentInterface::KEY_ADDITIONAL_DATA => [],
            ]
        ]);

        try {
            $this->paymentMethodManagement->set($cart->getId(), $payment);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        return [
            'cart' => [
                'cart_id' => $maskedCartId,
                'model' => $cart,
            ],
        ];
    }
}
