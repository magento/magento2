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
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CouponManagementInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\QuoteGraphQl\Model\Authorization\CartMutationInterface;

/**
 * {@inheritdoc}
 */
class ApplyCouponToCart implements ResolverInterface
{
    /**
     * @var CouponManagementInterface
     */
    private $couponManagement;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * @var CartMutationInterface
     */
    private $cartMutationAuthorization;

    /**
     * @param ValueFactory $valueFactory
     * @param CouponManagementInterface $couponManagement
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToId
     * @param CartMutationsAllowedInterface $cartMutationAuthorization
     */
    public function __construct(
        ValueFactory $valueFactory,
        CouponManagementInterface $couponManagement,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToId,
        CartMutationInterface $cartMutationAuthorization
    ) {
        $this->valueFactory = $valueFactory;
        $this->couponManagement = $couponManagement;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToId;
        $this->cartMutationAuthorization = $cartMutationAuthorization;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null) : Value
    {
        $maskedQuoteId = $args['input']['cart_id'];
        $couponCode = $args['input']['coupon_code'];

        if (!$maskedQuoteId) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }

        if (!$couponCode) {
            throw new GraphQlInputException(__('Required parameter "coupon_code" is missing'));
        }

        try {
            $cartId = $this->maskedQuoteIdToQuoteId->execute($maskedQuoteId);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__('Could not find a cart with the provided ID'));
        }

        if (!$this->cartMutationAuthorization->isAllowed($cartId)) {
            throw new GraphQlAuthorizationException(
                __('The current user cannot perform operations on the selected cart')
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
            'code' => $couponCode
        ];

        $result = function () use ($data) {
            return $data;
        };

        return $this->valueFactory->create($result);
    }
}
