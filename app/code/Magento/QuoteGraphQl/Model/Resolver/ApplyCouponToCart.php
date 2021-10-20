<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CouponManagementInterface;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;

/**
 * @inheritdoc
 */
class ApplyCouponToCart implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var CouponManagementInterface
     */
    private $couponManagement;

    /**
     * @param GetCartForUser $getCartForUser
     * @param CouponManagementInterface $couponManagement
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        CouponManagementInterface $couponManagement
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->couponManagement = $couponManagement;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }
        $maskedCartId = $args['input']['cart_id'];

        if (empty($args['input']['coupon_code'])) {
            throw new GraphQlInputException(__('Required parameter "coupon_code" is missing'));
        }
        $couponCode = $args['input']['coupon_code'];

        $currentUserId = $context->getUserId();
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $cart = $this->getCartForUser->execute($maskedCartId, $currentUserId, $storeId);
        $cartId = $cart->getId();

        /* Check current cart does not have coupon code applied */
        $appliedCouponCode = $this->couponManagement->get($cartId);
        if (!empty($appliedCouponCode)) {
            throw new GraphQlInputException(
                __('A coupon is already applied to the cart. Please remove it to apply another')
            );
        }

        try {
            $this->couponManagement->set($cartId, $couponCode);
        } catch (NoSuchEntityException $e) {
            $message = $e->getMessage();
            if (preg_match('/The "\d+" Cart doesn\'t contain products/', $message)) {
                $message = 'Cart does not contain products.';
            }
            throw new GraphQlNoSuchEntityException(__($message), $e);
        } catch (CouldNotSaveException $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }

        $cart = $this->getCartForUser->execute($maskedCartId, $currentUserId, $storeId);
        return [
            'cart' => [
                'model' => $cart,
            ],
        ];
    }
}
