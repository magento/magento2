<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Validator\EmailAddress as EmailAddressValidator;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;

/**
 * @inheritdoc
 */
class SetGuestEmailOnCart implements ResolverInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @param GetCartForUser $getCartForUser
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        CartRepositoryInterface $cartRepository
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->cartRepository = $cartRepository;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($args['input']['cart_id']) || empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }
        $maskedCartId = $args['input']['cart_id'];

        if (!isset($args['input']['email']) || empty($args['input']['email'])) {
            throw new GraphQlInputException(__('Required parameter "email" is missing'));
        }

        $guestEmail = $args['input']['email'];

        if (!\Zend_Validate::is($guestEmail, EmailAddressValidator::class)) {
            throw new GraphQlInputException(__('Invalid email format'));
        }

        $currentUserId = $context->getUserId();

        if ($currentUserId !== 0) {
            throw new GraphQlInputException(__('The request is not allowed for logged in customers'));
        }

        $cart = $this->getCartForUser->execute($maskedCartId, $currentUserId);
        $cart->setCustomerEmail($guestEmail);

        try {
            $this->cartRepository->save($cart);
        } catch (CouldNotSaveException $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }

        return [
            'cart' => [
                'model' => $cart,
            ],
        ];
    }
}
