<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
     * Check if postcode is valid
     *
     * @param string $countryId
     * @param string $postcode
     * @return bool
     */
    public function isValid($countryId, $postcode)
    {
        return $this->directoryHelper->isZipCodeOptional($countryId) || !empty($postcode);
    }
}
