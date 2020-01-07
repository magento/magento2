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
     * @param Data $helperData
     * @param Address $helperAddress
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Data $helperData,
        Address $helperAddress
    ) {
        $this->helperData= $helperData;
        $this->helperAddress= $helperAddress;
    }

    public function dataGetAttributeValidationClass($param)
    {
        return $this->dataAddress->getAttributeValidationClass($param);
    }

    public function addressGetAttributeValidationClass($param)
    {
        return $this->helperAddress->getAttributeValidationClass($param);
    }

    public function addressGetStreetLines()
    {
        return $this->helperAddress->getStreetLines();
    }

    public function addressIsVatAttributeVisible()
    {
        return $this->helperAddress->isVatAttributeVisible();
    }

    public function dataGetRegionJson()
    {
        return $this->helperData->getRegionJson();
    }

    public function dataGetCountriesWithOptionalZip()
    {
        return $this->helperData->getCountriesWithOptionalZip();
    }
}
