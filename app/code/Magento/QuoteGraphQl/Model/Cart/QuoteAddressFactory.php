<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\CustomerGraphQl\Model\Customer\Address\GetCustomerAddress;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\Quote\AddressFactory as BaseQuoteAddressFactory;

/**
 * Create QuoteAddress
 */
class QuoteAddressFactory
{
    /**
     * @var BaseQuoteAddressFactory
     */
    private $quoteAddressFactory;

    /**
     * @var GetCustomerAddress
     */
    private $getCustomerAddress;

    /**
     * @param BaseQuoteAddressFactory $quoteAddressFactory
     * @param GetCustomerAddress $getCustomerAddress
     */
    public function __construct(
        BaseQuoteAddressFactory $quoteAddressFactory,
        GetCustomerAddress $getCustomerAddress
    ) {
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->getCustomerAddress = $getCustomerAddress;
    }

    /**
     * Create QuoteAddress based on input data
     *
     * @param array $addressInput
     * @return QuoteAddress
     */
    public function createBasedOnInputData(array $addressInput): QuoteAddress
    {
        $addressInput['country_id'] = $addressInput['country_code'] ?? '';

        $quoteAddress = $this->quoteAddressFactory->create();
        $quoteAddress->addData($addressInput);
        return $quoteAddress;
    }

    /**
     * Create Quote Address based on Customer Address
     *
     * @param int $customerAddressId
     * @param int $customerId
     * @return QuoteAddress
     * @throws GraphQlInputException
     * @throws GraphQlAuthorizationException
     * @throws GraphQlNoSuchEntityException
     */
    public function createBasedOnCustomerAddress(int $customerAddressId, int $customerId): QuoteAddress
    {
        $customerAddress = $this->getCustomerAddress->execute((int)$customerAddressId, $customerId);

        $quoteAddress = $this->quoteAddressFactory->create();
        try {
            $quoteAddress->importCustomerAddressData($customerAddress);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }
        return $quoteAddress;
    }
}
