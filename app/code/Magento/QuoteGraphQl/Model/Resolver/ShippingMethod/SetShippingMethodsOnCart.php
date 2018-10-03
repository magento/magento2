<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\ShippingMethod;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\ShippingMethodManagementInterface;
use Magento\QuoteGraphQl\Model\Authorization\IsCartMutationAllowedForCurrentUser;

/**
 * Class SetShippingMethodsOnCart
 *
 * Mutation resolver for setting shipping methods for shopping cart
 */
class SetShippingMethodsOnCart implements ResolverInterface
{
    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var ShippingMethodManagementInterface
     */
    private $shippingMethodManagement;

    /**
     * @var IsCartMutationAllowedForCurrentUser
     */
    private $isCartMutationAllowedForCurrentUser;

    /**
     * SetShippingMethodsOnCart constructor.
     * @param ArrayManager $arrayManager
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param ShippingMethodManagementInterface $shippingMethodManagement
     */
    public function __construct(
        ArrayManager $arrayManager,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        ShippingMethodManagementInterface $shippingMethodManagement,
        IsCartMutationAllowedForCurrentUser $isCartMutationAllowedForCurrentUser
    ) {
        $this->arrayManager = $arrayManager;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->isCartMutationAllowedForCurrentUser = $isCartMutationAllowedForCurrentUser;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $shippingMethods = $this->arrayManager->get('input/shipping_methods', $args);
        $maskedCartId = $this->arrayManager->get('input/cart_id', $args);

        if (!$maskedCartId) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }

        if (!$shippingMethods) {
            throw new GraphQlInputException(__('Required parameter "shipping_methods" is missing'));
        }

        $shippingMethod = reset($shippingMethods); // TODO: provide implementation for multishipping

        if (!$shippingMethod['shipping_carrier_code']) { // FIXME: check the E_WARNING here
            throw new GraphQlInputException(__('Required parameter "shipping_carrier_code" is missing'));
        }

        if (!$shippingMethod['shipping_method_code']) { // FIXME: check the E_WARNING here
            throw new GraphQlInputException(__('Required parameter "shipping_method_code" is missing'));
        }

        try {
            $cartId = $this->maskedQuoteIdToQuoteId->execute((string) $maskedCartId);
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
            $this->shippingMethodManagement->set(
                $cartId,
                $shippingMethods['shipping_carrier_code'],
                $shippingMethods['shipping_method_code']
            );
        } catch (InputException $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        } catch (CouldNotSaveException $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        } catch (StateException $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        }

        return 'Success!'; // TODO we should return cart here
    }
}