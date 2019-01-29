<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\Payment\PaymentDataProvider;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;

/**
 * @inheritdoc
 */
class CartPaymentMethod implements ResolverInterface
{
    /**
     * @var PaymentDataProvider
     */
    private $paymentDataProvider;

    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @param PaymentDataProvider $paymentDataProvider
     * @param GetCartForUser $getCartForUser
     */
    public function __construct(
        PaymentDataProvider $paymentDataProvider,
        GetCartForUser $getCartForUser
    ) {
        $this->paymentDataProvider = $paymentDataProvider;
        $this->getCartForUser = $getCartForUser;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['cart_id'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $maskedCartId = $value['cart_id'];
        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId());

        return $this->paymentDataProvider->getCartPayment($cart);
    }
}
