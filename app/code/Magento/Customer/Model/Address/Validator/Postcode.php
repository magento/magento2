<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Address\Validator;

class Postcode
{
    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @param \Magento\Directory\Helper\Data $directoryHelper
     */
    public function __construct(\Magento\Directory\Helper\Data $directoryHelper)
    {
        $this->directoryHelper = $directoryHelper;
    }

    /**
     * Check if zip code valid
     *
     * @param string $countryId
     * @param string $zip
     * @return bool
     */
    public function isValid($countryId, $zip)
    {
        return $this->directoryHelper->isZipCodeOptional($countryId) || !empty($zip);
    }
}
