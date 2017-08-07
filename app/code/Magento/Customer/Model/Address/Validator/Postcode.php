<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Address\Validator;

/**
 * Class \Magento\Customer\Model\Address\Validator\Postcode
 *
 * @since 2.1.0
 */
class Postcode
{
    /**
     * @var \Magento\Directory\Helper\Data
     * @since 2.1.0
     */
    protected $directoryHelper;

    /**
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @since 2.1.0
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
     * @since 2.1.0
     */
    public function isValid($countryId, $postcode)
    {
        return $this->directoryHelper->isZipCodeOptional($countryId) || !empty($postcode);
    }
}
