<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Validator\EmailAddress as EmailAddressValidator;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\QuoteGraphQl\Model\Cart\CheckCartCheckoutAllowance;
use Magento\QuoteGraphQl\Model\Cart\SetGuestEmailOnCart as SetGuestEmailOnCartModel;

/**
 * @inheritdoc
 */
class SetGuestEmailOnCart implements ResolverInterface
{
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
     * @var SetGuestEmailOnCartModel
     */
    private $setGuestEmailOnCartModel;

    /**
     * @param GetCartForUser $getCartForUser
     * @param CartRepositoryInterface $cartRepository
     * @param EmailAddressValidator $emailValidator
     * @param CheckCartCheckoutAllowance $checkCartCheckoutAllowance
     * @param SetGuestEmailOnCartModel $setGuestEmailOnCartModel
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        EmailAddressValidator $emailValidator,
        CheckCartCheckoutAllowance $checkCartCheckoutAllowance,
        SetGuestEmailOnCartModel $setGuestEmailOnCartModel
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->emailValidator = $emailValidator;
        $this->checkCartCheckoutAllowance = $checkCartCheckoutAllowance;
        $this->setGuestEmailOnCartModel = $setGuestEmailOnCartModel;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
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

        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $cart = $this->getCartForUser->execute($maskedCartId, $currentUserId, $storeId);
        $this->checkCartCheckoutAllowance->execute($cart);

        $this->setGuestEmailOnCartModel->execute($context, $cart, $email);

        return [
            'cart' => [
                'model' => $cart,
            ],
        ];
    }
}
