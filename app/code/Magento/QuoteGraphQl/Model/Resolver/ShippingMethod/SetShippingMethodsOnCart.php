<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\ShippingMethod;

use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Checkout\Model\ShippingInformation;
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
use Magento\QuoteGraphQl\Model\Authorization\IsCartMutationAllowedForCurrentUser;
use Magento\Quote\Model\Quote\AddressFactory as QuoteAddressFactory;
use Magento\Quote\Model\ResourceModel\Quote\Address as QuoteAddressResource;
use Magento\Checkout\Model\ShippingInformationFactory;

/**
 * Class SetShippingMethodsOnCart
 *
 * Mutation resolver for setting shipping methods for shopping cart
 */
class SetShippingMethodsOnCart implements ResolverInterface
{
    /**
     * @var ShippingInformationFactory
     */
    private $shippingInformationFactory;

    /**
     * @var QuoteAddressFactory
     */
    private $quoteAddressFactory;

    /**
     * @var QuoteAddressResource
     */
    private $quoteAddressResource;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var IsCartMutationAllowedForCurrentUser
     */
    private $isCartMutationAllowedForCurrentUser;

    /**
     * @var ShippingInformationManagementInterface
     */
    private $shippingInformationManagement;

    /**
     * SetShippingMethodsOnCart constructor.
     * @param ArrayManager $arrayManager
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param IsCartMutationAllowedForCurrentUser $isCartMutationAllowedForCurrentUser
     */
    public function __construct(
        ArrayManager $arrayManager,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        IsCartMutationAllowedForCurrentUser $isCartMutationAllowedForCurrentUser,
        ShippingInformationManagementInterface $shippingInformationManagement,
        QuoteAddressFactory $quoteAddressFacrory,
        QuoteAddressResource $quoteAddressResource,
        ShippingInformationFactory $shippingInformationFactory
    ) {
        $this->arrayManager = $arrayManager;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->isCartMutationAllowedForCurrentUser = $isCartMutationAllowedForCurrentUser;
        $this->shippingInformationManagement = $shippingInformationManagement;

        $this->quoteAddressResource = $quoteAddressResource;
        $this->quoteAddressFactory = $quoteAddressFacrory;
        $this->shippingInformationFactory = $shippingInformationFactory;
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

        if (!$shippingMethod['cart_address_id']) {
            throw new GraphQlInputException(__('Required parameter "cart_address_id" is missing'));
        }
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

        $quoteAddress = $this->quoteAddressFactory->create();
        $this->quoteAddressResource->load($quoteAddress, $shippingMethod['cart_address_id']);

        /** @var ShippingInformation $shippingInformation */
        $shippingInformation = $this->shippingInformationFactory->create();

        /* If the address is not a shipping address (but billing) the system will find the proper shipping address for
           the selected cart and set the information there (actual for single shipping address) */
        $shippingInformation->setShippingAddress($quoteAddress);
        $shippingInformation->setShippingCarrierCode($shippingMethod['shipping_carrier_code']);
        $shippingInformation->setShippingMethodCode($shippingMethod['shipping_method_code']);

        try {
            $this->shippingInformationManagement->saveAddressInformation($cartId, $shippingInformation);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (StateException $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        } catch (InputException $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        }

        return [
            'cart' => [
                'cart_id' => $maskedCartId
            ]
        ];
    }
}
