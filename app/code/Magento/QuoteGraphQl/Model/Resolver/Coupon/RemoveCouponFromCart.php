<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\Coupon;

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
use Magento\QuoteGraphQl\Model\CartMutationsAllowedInterface;

/**
 * {@inheritdoc}
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
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var CartMutationsAllowedInterface
     */
    private $cartMutationsAllowed;

    /**
     * @param ValueFactory $valueFactory
     * @param CouponManagementInterface $couponManagement
     * @param CartMutationsAllowedInterface $cartMutationsAllowed
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToId
     */
    public function __construct(
        ValueFactory $valueFactory,
        CouponManagementInterface $couponManagement,
        CartMutationsAllowedInterface $cartMutationsAllowed,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToId
    ) {
        $this->valueFactory = $valueFactory;
        $this->couponManagement = $couponManagement;
        $this->cartMutationsAllowed = $cartMutationsAllowed;
        $this->maskedQuoteIdToId = $maskedQuoteIdToId;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null) : Value
    {
        $maskedCartId = $args['input']['cart_id'];

        if (!$maskedCartId) {
            throw new GraphQlInputException(__('Required parameter is missing'));
        }

        try {
            $cartId = $this->maskedQuoteIdToId->execute($maskedCartId);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__('No cart with provided ID found'));
        }

        if (!$this->cartMutationsAllowed->execute((int) $cartId)) {
            throw new GraphQlAuthorizationException(
                __('Operations with selected card is not permitted for current user')
            );
        }

        try {
            $this->couponManagement->remove($cartId);
        } catch (\Exception $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        }

        $data['cart']['applied_coupon'] = [
            'code' => ''
        ];

        $result = function () use ($data) {
            return $data;
        };

        return $this->valueFactory->create($result);
    }
}
