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
     * @var CartMutationInterface
     */
    private $cartMutationAuthorization;

    /**
     * @param ValueFactory $valueFactory
     * @param CouponManagementInterface $couponManagement
     * @param CartMutationInterface $cartMutationAuthorization
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToId
     */
    public function __construct(
        ValueFactory $valueFactory,
        CouponManagementInterface $couponManagement,
        CartMutationInterface $cartMutationAuthorization,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToId
    ) {
        $this->valueFactory = $valueFactory;
        $this->couponManagement = $couponManagement;
        $this->cartMutationAuthorization = $cartMutationAuthorization;
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
            throw new GraphQlNoSuchEntityException(__('Could not find a cart with the provided ID'));
        }

        if (!$this->cartMutationAuthorization->isAllowed((int) $cartId)) {
            throw new GraphQlAuthorizationException(
                __('The current user cannot perform operations on the selected cart')
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
            'code' => ''
        ];

        $result = function () use ($data) {
            return $data;
        };

        return $this->valueFactory->create($result);
    }
}
