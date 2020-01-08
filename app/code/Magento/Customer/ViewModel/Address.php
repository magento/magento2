<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\ViewModel;

use Magento\Directory\Helper\Data as DataHelper;
use Magento\Customer\Helper\Address as AddressHelper;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Customer address view model.
 */

 /**
  * Address view model
  */
class Address implements ArgumentInterface
{
    /**
     * Data helper
     *
     * @var DataHelper
     */
    private $helperData;

    /**
     * Address helper
     *
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
     * Returns data validation class
     *
     * @param mixed $param
     * @return mixed
     */
    public function dataGetAttributeValidationClass($param)
    {
        return $this->helperData->getAttributeValidationClass($param);
    }

    /**
     * Returns address validation class
     *
     * @param mixed $param
     * @return mixed
     */
    public function addressGetAttributeValidationClass($param)
    {
        return $this->helperAddress->getAttributeValidationClass($param);
    }

    /**
     * Returns street lines
     *
     * @return mixed
     */
    public function addressGetStreetLines()
    {
        return $this->helperAddress->getStreetLines();
    }

    /**
     * Returns if VAT attribute is visible
     *
     * @return boolean
     */
    public function addressIsVatAttributeVisible()
    {
        return $this->helperAddress->isVatAttributeVisible();
    }

    /**
     * Returns region JSON
     *
     * @return mixed
     */
    public function dataGetRegionJson()
    {
        return $this->helperData->getRegionJson();
    }

    /**
     * Returns rcountries with optional zip
     *
     * @return mixed
     */
    public function dataGetCountriesWithOptionalZip()
    {
        return $this->helperData->getCountriesWithOptionalZip();
    }
}
