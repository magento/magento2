<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Customer\Helper\Address as AddressHelper;
use Magento\CustomerGraphQl\Model\Customer\Address\GetCustomerAddress;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\Quote\AddressFactory as BaseQuoteAddressFactory;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Directory\Helper\Data as CountryHelper;
use Magento\Directory\Model\AllowedCountries;

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
     * @var RegionCollectionFactory
     */
    private $regionCollectionFactory;

    /**
     * @var CountryHelper
     */
    private $countryHelper;

    /**
     * @var AllowedCountries
     */
    private $allowedCountries;

    /**
     * @param BaseQuoteAddressFactory $quoteAddressFactory
     * @param GetCustomerAddress $getCustomerAddress
     * @param AddressHelper $addressHelper
     * @param RegionCollectionFactory $regionCollectionFactory
     * @param CountryHelper $countryHelper
     * @param AllowedCountries $allowedCountries
     */
    public function __construct(
        BaseQuoteAddressFactory $quoteAddressFactory,
        GetCustomerAddress $getCustomerAddress,
        AddressHelper $addressHelper,
        RegionCollectionFactory $regionCollectionFactory,
        CountryHelper $countryHelper,
        AllowedCountries $allowedCountries
    ) {
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->getCustomerAddress = $getCustomerAddress;
        $this->addressHelper = $addressHelper;
        $this->regionCollectionFactory = $regionCollectionFactory;
        $this->countryHelper = $countryHelper;
        $this->allowedCountries = $allowedCountries;
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

        $allowedCountries = $this->allowedCountries->getAllowedCountries();
        if (!in_array($addressInput['country_code'], $allowedCountries, true)) {
            throw new GraphQlInputException(__('Country is not available'));
        }
        $isRegionRequired = $this->countryHelper->isRegionRequired($addressInput['country_code']);
        if ($isRegionRequired && !empty($addressInput['region'])) {
            $regionCollection = $this->regionCollectionFactory
                ->create()
                ->addRegionCodeFilter($addressInput['region'])
                ->addCountryFilter($addressInput['country_code']);
            if ($regionCollection->getSize() === 0) {
                throw new GraphQlInputException(
                    __('Region is not available for the selected country')
                );
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
