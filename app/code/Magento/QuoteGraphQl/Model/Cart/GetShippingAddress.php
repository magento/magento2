<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Quote\Model\Quote\Address;

/**
 * Get shipping address
 */
class GetShippingAddress
{
    /**
     * @var QuoteAddressFactory
     */
    private $quoteAddressFactory;

    /**
     * @param QuoteAddressFactory $quoteAddressFactory
     */
    public function __construct(QuoteAddressFactory $quoteAddressFactory)
    {
        $this->quoteAddressFactory = $quoteAddressFactory;
    }

    /**
     * @param ContextInterface $context
     * @param array $shippingAddressInput
     * @return Address
     * @throws GraphQlAuthorizationException
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     */
    public function execute(ContextInterface $context, array $shippingAddressInput)
    {
        $customerAddressId = $shippingAddressInput['customer_address_id'] ?? null;

        $addressInput = $shippingAddressInput['address'];
        if ($addressInput) {
            $addressInput['customer_notes'] = $shippingAddressInput['customer_notes'] ?? '';
        }

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
