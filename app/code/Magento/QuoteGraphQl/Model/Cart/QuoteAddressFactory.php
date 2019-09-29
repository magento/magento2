<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Customer\Helper\Address as AddressHelper;
use Magento\CustomerGraphQl\Model\Customer\Address\GetCustomerAddress;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\Quote\AddressFactory as BaseQuoteAddressFactory;
use Magento\Framework\App\ObjectManager;

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
     * @var AddressHelper
     */
    private $addressHelper;

    /**
     * @var CountryInformationAcquirerInterface
     */
    private $countryInformationAcquirer;

    /**
     * @param BaseQuoteAddressFactory $quoteAddressFactory
     * @param GetCustomerAddress $getCustomerAddress
     * @param AddressHelper $addressHelper
     * @param CountryInformationAcquirerInterface|null $countryInformationAcquirer
     */
    public function __construct(
        BaseQuoteAddressFactory $quoteAddressFactory,
        GetCustomerAddress $getCustomerAddress,
        AddressHelper $addressHelper,
        CountryInformationAcquirerInterface $countryInformationAcquirer = null
    ) {
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->getCustomerAddress = $getCustomerAddress;
        $this->addressHelper = $addressHelper;
        $this->countryInformationAcquirer = $countryInformationAcquirer;
        $this->countryInformationAcquirer = $countryInformationAcquirer
            ?: ObjectManager::getInstance()->get(CountryInformationAcquirerInterface::class);
    }

    /**
     * Create QuoteAddress based on input data
     *
     * @param array $addressInput
     *
     * @return QuoteAddress
     * @throws GraphQlInputException
     */
    public function createBasedOnInputData(array $addressInput): QuoteAddress
    {
        $addressInput['country_id'] = '';
        if (isset($addressInput['country_code']) && $addressInput['country_code']) {
            $addressInput['country_code'] = strtoupper($addressInput['country_code']);
            $addressInput['country_id'] = $addressInput['country_code'];
        }

        if ($addressInput['country_id'] && isset($addressInput['region'])) {
            $countryInformation = $this->countryInformationAcquirer->getCountryInfo($addressInput['country_id']);
            $availableRegions = $countryInformation->getAvailableRegions();
            if (null !== $availableRegions) {
                $addressInput['region_code'] = $addressInput['region'];
            }
        }

        $maxAllowedLineCount = $this->addressHelper->getStreetLines();
        if (is_array($addressInput['street']) && count($addressInput['street']) > $maxAllowedLineCount) {
            throw new GraphQlInputException(
                __('"Street Address" cannot contain more than %1 lines.', $maxAllowedLineCount)
            );
        }

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
     * @throws GraphQlAuthorizationException
     * @throws GraphQlInputException
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
