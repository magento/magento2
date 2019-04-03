<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Customer\Api\Data\AddressInterface as CustomerAddress;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\Quote\AddressFactory as BaseQuoteAddressFactory;
use Magento\QuoteGraphQl\Model\Cart\QuoteAddress\Validator;

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
     * @var Validator
     */
    private $quoteAddressValidator;

    /**
     * @param BaseQuoteAddressFactory $quoteAddressFactory
     * @param Validator $quoteAddressValidator
     */
    public function __construct(
        BaseQuoteAddressFactory $quoteAddressFactory,
        Validator $quoteAddressValidator
    ) {
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->quoteAddressValidator = $quoteAddressValidator;
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
        $this->quoteAddressValidator->validate($quoteAddress);

        return $quoteAddress;
    }

    /**
     * Create QuoteAddress based on CustomerAddress
     *
     * @param CustomerAddress $customerAddress
     * @return QuoteAddress
     * @throws GraphQlInputException
     */
    public function createBasedOnCustomerAddress(CustomerAddress $customerAddress): QuoteAddress
    {
        $quoteAddress = $this->quoteAddressFactory->create();
        try {
            $quoteAddress->importCustomerAddressData($customerAddress);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }
        $this->quoteAddressValidator->validate($quoteAddress);

        return $quoteAddress;
    }
}
