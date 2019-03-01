<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\Quote\AddressFactory as QuoteAddressFactory;
use Magento\Quote\Model\ResourceModel\Quote\Address as QuoteAddressResource;


/**
 * Get quote address
 */
class GetQuoteAddress
{
    /**
     * @var QuoteAddressFactory
     */
    private $quoteAddressFactory;

    /**
     * @var QuoteAddressResource
     */
    private $quoteAddressResource;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(AddressRepositoryInterface $addressRepository)
    {
        $this->addressRepository = $addressRepository;
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
    public function execute(int $quoteAddressId, ?int $customerId): QuoteAddress
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
