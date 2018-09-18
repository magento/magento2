<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\Coupon;

use Magento\Framework\Exception\CouldNotDeleteException;
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
class RemoveCouponFromCart implements ResolverInterface
{
    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToId;

    /**
     * @var CouponManagementInterface
     */
    private $couponManagement;

    /**
     * @var IsCartMutationAllowedForCurrentUser
     */
    private $isCartMutationAllowedForCurrentUser;

    /**
     * @param CouponManagementInterface $couponManagement
     * @param IsCartMutationAllowedForCurrentUser $isCartMutationAllowedForCurrentUser
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToId
     */
    public function __construct(
        CouponManagementInterface $couponManagement,
        IsCartMutationAllowedForCurrentUser $isCartMutationAllowedForCurrentUser,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToId
    ) {
        $this->couponManagement = $couponManagement;
        $this->isCartMutationAllowedForCurrentUser = $isCartMutationAllowedForCurrentUser;
        $this->maskedQuoteIdToId = $maskedQuoteIdToId;
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

        try {
            $cartId = $this->maskedQuoteIdToId->execute($maskedCartId);
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

        try {
            $this->couponManagement->remove($cartId);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (CouldNotDeleteException $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        }

        $data['cart']['applied_coupon'] = [
            'code' => '',
        ];
        return $data;
    }
}
