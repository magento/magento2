<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\Coupon;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CouponManagementInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\QuoteGraphQl\Model\Authorization\IsCartMutationAllowedForCurrentUser;

/**
 * @inheritdoc
 */
class ApplyCouponToCart implements ResolverInterface
{
    /**
     * @var CouponManagementInterface
     */
    private $couponManagement;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * @var IsCartMutationAllowedForCurrentUser
     */
    private $isCartMutationAllowedForCurrentUser;

    /**
     * @param CouponManagementInterface $couponManagement
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToId
     * @param IsCartMutationAllowedForCurrentUser $isCartMutationAllowedForCurrentUser
     */
    public function __construct(
        CouponManagementInterface $couponManagement,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToId,
        IsCartMutationAllowedForCurrentUser $isCartMutationAllowedForCurrentUser
    ) {
        $this->couponManagement = $couponManagement;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToId;
        $this->isCartMutationAllowedForCurrentUser = $isCartMutationAllowedForCurrentUser;
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

        if (!isset($args['input']['coupon_code'])) {
            throw new GraphQlInputException(__('Required parameter "coupon_code" is missing'));
        }
        $couponCode = $args['input']['coupon_code'];

        try {
            $cartId = $this->maskedQuoteIdToQuoteId->execute($maskedCartId);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(
                __('Could not find a cart with ID "%masked_cart_id"', ['masked_cart_id' => $maskedCartId])
            );
        }

        if (false === $this->isCartMutationAllowedForCurrentUser->execute($cartId)) {
            throw new GraphQlAuthorizationException(
                __(
                    'The current user cannot perform operations on cart "%masked_cart_id"',
                    ['masked_cart_id' => $maskedCartId]
                )
            );
        }

        /* Check current cart does not have coupon code applied */
        $appliedCouponCode = $this->couponManagement->get($cartId);
        if (!empty($appliedCouponCode)) {
            throw new GraphQlInputException(
                __('A coupon is already applied to the cart. Please remove it to apply another')
            );
        }

        try {
            $this->couponManagement->set($cartId, $couponCode);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (CouldNotSaveException $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        }

        $data['cart']['applied_coupon'] = [
            'code' => $couponCode,
        ];
        return $data;
    }
}
