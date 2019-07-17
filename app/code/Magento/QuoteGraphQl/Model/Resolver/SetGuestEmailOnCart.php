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
use Magento\QuoteGraphQl\Model\Cart\CheckCartCheckoutAllowance;

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
     * @var EmailAddressValidator
     */
    private $emailValidator;

    /**
     * @var CheckCartCheckoutAllowance
     */
    private $checkCartCheckoutAllowance;

    /**
     * @param GetCartForUser $getCartForUser
     * @param CartRepositoryInterface $cartRepository
     * @param EmailAddressValidator $emailValidator
     * @param CheckCartCheckoutAllowance $checkCartCheckoutAllowance
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        CartRepositoryInterface $cartRepository,
        EmailAddressValidator $emailValidator,
        CheckCartCheckoutAllowance $checkCartCheckoutAllowance
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->cartRepository = $cartRepository;
        $this->emailValidator = $emailValidator;
        $this->checkCartCheckoutAllowance = $checkCartCheckoutAllowance;
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

        if (empty($args['input']['email'])) {
            throw new GraphQlInputException(__('Required parameter "email" is missing'));
        }

        if (false === $this->emailValidator->isValid($args['input']['email'])) {
            throw new GraphQlInputException(__('Invalid email format'));
        }
        $email = $args['input']['email'];

        $currentUserId = $context->getUserId();

        if ($currentUserId !== 0) {
            throw new GraphQlInputException(__('The request is not allowed for logged in customers'));
        }

        $cart = $this->getCartForUser->execute($maskedCartId, $currentUserId);
        $this->checkCartCheckoutAllowance->execute($cart);
        $cart->setCustomerEmail($email);

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
