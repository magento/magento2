<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Model\Cart\CustomerCartResolver;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;

/**
 * Assign the customer to the guest cart resolver
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class AssignCustomerToGuestCart implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var CustomerCartResolver
     */
    private $customerCartResolver;

    /**
     * @var GuestCartManagementInterface
     */
    private $guestCartManagementInterface;

    /**
     * @param GetCartForUser $getCartForUser
     * @param GuestCartManagementInterface $guestCartManagementInterface
     * @param CustomerCartResolver $customerCartResolver
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        GuestCartManagementInterface $guestCartManagementInterface,
        CustomerCartResolver $customerCartResolver
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->guestCartManagementInterface = $guestCartManagementInterface;
        $this->customerCartResolver = $customerCartResolver;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__(
                'The current customer isn\'t authorized.'
            ));
        }

        $currentUserId = $context->getUserId();
        $guestMaskedCartId = $args['cart_id'];
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

        $this->getCartForUser->execute($guestMaskedCartId, null, $storeId);

        try {
            $this->guestCartManagementInterface->assignCustomer($guestMaskedCartId, $currentUserId, $storeId);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(
                __('Could not find a cart with ID "%masked_cart_id"', ['masked_cart_id' => $guestMaskedCartId]),
                $e
            );
        } catch (StateException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(
                __('Unable to assign the customer to the guest cart: %message', ['message' => $e->getMessage()]),
                $e
            );
        }

        try {
            $customerCart = $this->customerCartResolver->resolve($currentUserId);
        } catch (\Exception $e) {
            $customerCart = null;
        }
        return [
            'model' => $customerCart,
        ];
    }
}
