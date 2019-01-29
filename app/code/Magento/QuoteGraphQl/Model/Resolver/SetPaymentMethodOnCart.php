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
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\QuoteGraphQl\Model\Cart\Payment\MethodBuilder;

/**
 * Mutation resolver for setting payment method for shopping cart
 */
class SetPaymentMethodOnCart implements ResolverInterface
{
    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

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
     * @var MethodBuilder
     */
    private $methodBuilder;

    /**
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param GetCartForUser $getCartForUser
     * @param ArrayManager $arrayManager
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     * @param MethodBuilder $methodBuilder
     */
    public function __construct(
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        GetCartForUser $getCartForUser,
        ArrayManager $arrayManager,
        PaymentMethodManagementInterface $paymentMethodManagement,
        MethodBuilder $methodBuilder
    ) {
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->getCartForUser = $getCartForUser;
        $this->arrayManager = $arrayManager;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->methodBuilder = $methodBuilder;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $paymentMethod = $this->arrayManager->get('input/payment_method', $args);
        $maskedCartId = $this->arrayManager->get('input/cart_id', $args);

        if (!$maskedCartId) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }
        if (!$paymentMethod) {
            throw new GraphQlInputException(__('Required parameter "payment_method" is missing'));
        }

        $maskedCartId = $args['input']['cart_id'];
        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId());

        try {
            $this->paymentMethodManagement->set($cart->getId(), $this->methodBuilder->build($args));
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        return [
            'cart' => [
                'cart_id' => $maskedCartId,
            ],
        ];
    }
}
