<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Model\ResourceModel\Quote\Address as AddressResource;

/**
 * Get quote address
 */
class GetQuoteAddress
{
    /**
     * @var AddressInterfaceFactory
     */
    private $quoteAddressFactory;

    /**
     * @var AddressResource
     */
    private $quoteAddressResource;

    /**
     * @param AddressInterfaceFactory $quoteAddressFactory
     * @param AddressResource $quoteAddressResource
     */
    public function __construct(
        AddressInterfaceFactory $quoteAddressFactory,
        AddressResource $quoteAddressResource
    ) {
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->quoteAddressResource = $quoteAddressResource;
    }

    /**
     * Get quote address
     *
     * @param int $quoteAddressId
     * @param int|null $customerId
     * @return AddressInterface
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     * @throws GraphQlAuthorizationException
     */
    public function execute(int $quoteAddressId, ?int $customerId): AddressInterface
    {
        $quoteAddress = $this->quoteAddressFactory->create();

        $this->quoteAddressResource->load($quoteAddress, $quoteAddressId);
        if (null === $quoteAddress->getId()) {
            throw new GraphQlNoSuchEntityException(
                __('Could not find a cart address with ID "%cart_address_id"', ['cart_address_id' => $quoteAddressId])
            );
        }

        $quoteAddressCustomerId = (int)$quoteAddress->getCustomerId();

        /* Guest cart, allow operations */
        if (!$quoteAddressCustomerId && null === $customerId) {
            return $quoteAddress;
        }

        if ($quoteAddressCustomerId !== $customerId) {
            throw new GraphQlAuthorizationException(
                __(
                    'The current user cannot use cart address with ID "%cart_address_id"',
                    ['cart_address_id' => $quoteAddressId]
                )
            );
        }
        return $quoteAddress;
    }
}
