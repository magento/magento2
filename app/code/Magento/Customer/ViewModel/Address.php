<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\ViewModel;

use Magento\Directory\Helper\Data as DataHelper;
use Magento\Customer\Helper\Address as AddressHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;

 /**
  * Custom address view model
  */
class Address implements ArgumentInterface
{
    /**
     * @var DataHelper
     */
    private $helperData;

    /**
     * @var AddressHelper
     */
    private $helperAddress;

    /**
     * Constructor
     *
     * @param DataHelper $helperData
     * @param AddressHelper $helperAddress
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        DataHelper $helperData,
        AddressHelper $helperAddress
    ) {
        $this->helperData= $helperData;
        $this->helperAddress= $helperAddress;
    }

    /**
     * Get string with frontend validation classes for attribute
     *
     * @param string $attributeCode
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function dataGetAttributeValidationClass($attributeCode)
    {
        return $this->helperData->getAttributeValidationClass($attributeCode);
    }

    /**
     * Get string with frontend validation classes for attribute
     *
     * @param string $attributeCode
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addressGetAttributeValidationClass($attributeCode)
    {
        return $this->helperAddress->getAttributeValidationClass($attributeCode);
    }

    /**
     * Return Number of Lines in a Street Address for store
     *
     * @param \Magento\Store\Model\Store|int|string $store
     *
     * @return int
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addressGetStreetLines()
    {
        return $this->helperAddress->getStreetLines();
    }

    /**
     * Check if VAT ID address attribute has to be shown on frontend (on Customer Address management forms)
     *
     * @return boolean
     */
    public function addressIsVatAttributeVisible()
    {
        return $this->helperAddress->isVatAttributeVisible();
    }

    /**
     * Retrieve regions data json
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function dataGetRegionJson()
    {
        return $this->helperData->getRegionJson();
    }

    /**
     * Return ISO2 country codes, which have optional Zip/Postal pre-configured
     *
     * @param bool $asJson
     * @return array|string
     */
    public function dataGetCountriesWithOptionalZip($asJson)
    {
        return $this->helperData->getCountriesWithOptionalZip($asJson);
    }
}
