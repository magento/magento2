<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\CouldNotDeleteException;
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
class RemoveCouponFromCart implements ResolverInterface
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

        $currentUserId = $context->getUserId();
        $cart = $this->getCartForUser->execute($maskedCartId, $currentUserId);
        $cartId = $cart->getId();

        try {
            $this->couponManagement->remove($cartId);
        } catch (NoSuchEntityException $e) {
            $message = $e->getMessage();
            if (preg_match('/The "\d+" Cart doesn\'t contain products/', $message)) {
                $message = 'Cart does not contain products';
            }
            throw new GraphQlNoSuchEntityException(__($message), $e);
        } catch (CouldNotDeleteException $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }

        return [
            'cart' => [
                'model' => $cart,
            ],
        ];
    }
}
