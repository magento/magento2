<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Quote\Model\Quote\Address;

/**
 * Model for getting shipping address
 */
class GetShippingAddress
{
    /**
     * GetShippingAddress Constructor
     *
     * @param QuoteAddressFactory $quoteAddressFactory
     */
    public function __construct(
        private readonly QuoteAddressFactory $quoteAddressFactory
    ) {
    }

    /**
     * Get Shipping Address based on the input.
     *
     * @param ContextInterface $context
     * @param array $shippingAddressInput
     * @return Address
     * @throws GraphQlAuthorizationException
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     */
    public function execute(ContextInterface $context, array $shippingAddressInput): Address
    {
        $customerAddressId = $shippingAddressInput['customer_address_id'] ?? null;
        $customerAddressUID = $shippingAddressInput['customer_address_uid'] ?? null;
        $addressInput = $shippingAddressInput['address'] ?? null;

        if ($addressInput) {
            $addressInput['customer_notes'] = $shippingAddressInput['customer_notes'] ?? '';
        }

        if (empty($customerAddressId) && empty($customerAddressUID) && empty($addressInput)) {
            throw new GraphQlInputException(
                __('The shipping address must contain either "customer_address_id" or '
                    . '"customer_address_uid" or "address".')
            );
        }

        if ((!empty($customerAddressId) || !empty($customerAddressUID)) && !empty($addressInput)) {
            throw new GraphQlInputException(
                __('The shipping address cannot contain "customer_address_id" or ' .
                    '"customer_address_uid" together with "address".')
            );
        }

        return $this->createShippingAddress($context, $customerAddressId, $addressInput);
    }

    /**
     * Create shipping address.
     *
     * @param ContextInterface $context
     * @param int|null $customerAddressId
     * @param array|null $addressInput
     * @return Address
     * @throws GraphQlAuthorizationException
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     */
    private function createShippingAddress(
        ContextInterface $context,
        ?int $customerAddressId,
        ?array $addressInput
    ): Address {
        if (null === $customerAddressId) {
            $shippingAddress = $this->quoteAddressFactory->createBasedOnInputData($addressInput);
        } else {
            if (false === $context->getExtensionAttributes()->getIsCustomer()) {
                throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
            }
            $shippingAddress = $this->quoteAddressFactory->createBasedOnCustomerAddress(
                (int)$customerAddressId,
                $context->getUserId()
            );
        }

        return $shippingAddress;
    }
}
