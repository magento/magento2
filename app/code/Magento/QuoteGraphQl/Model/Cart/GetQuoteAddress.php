<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Api\Data\CartInterface;
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
     * @param CartInterface $cart
     * @param int $quoteAddressId
     * @param int|null $customerId
     * @return AddressInterface
     * @throws GraphQlAuthorizationException
     * @throws GraphQlNoSuchEntityException
     */
    public function execute(CartInterface $cart, int $quoteAddressId, ?int $customerId): AddressInterface
    {
        $quoteAddress = $this->quoteAddressFactory->create();

        $this->quoteAddressResource->load($quoteAddress, $quoteAddressId);
        if (null === $quoteAddress->getId()) {
            throw new GraphQlNoSuchEntityException(
                __('Could not find a cart address with ID "%cart_address_id"', ['cart_address_id' => $quoteAddressId])
            );
        }

        // TODO: GetQuoteAddress::execute should depend only on AddressInterface contract
        // https://github.com/magento/graphql-ce/issues/550
        if ($quoteAddress->getQuoteId() !== $cart->getId()) {
            throw new GraphQlNoSuchEntityException(
                __('Cart does not contain address with ID "%cart_address_id"', ['cart_address_id' => $quoteAddressId])
            );
        }

        if ((int)$quoteAddress->getCustomerId() !== (int)$customerId) {
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
