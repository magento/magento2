<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model\Onepage\Country\Postcode;

class Validator 
{
    /**
     * @var \Magento\Directory\Model\Country\Postcode\Config
     */
    protected $postCodesConfig;

    /**
     * @param \Magento\Directory\Model\Country\Postcode\Config $postCodesConfig
     */
    public function __construct(\Magento\Directory\Model\Country\Postcode\Config $postCodesConfig)
    {
        $this->postCodesConfig = $postCodesConfig;
    }

    /**
     * Validate postcode for selected country by mask
     *
     * @param string $postcode
     * @param string $countryId
     * @return bool
     */
    public function validate($postcode, $countryId)
    {
        $postCodes = $this->postCodesConfig->getPostCodes();
        if (isset($postCodes[$countryId]) && is_array($postCodes[$countryId])) {
            $patterns = $postCodes[$countryId];
            foreach ($patterns as $pattern) {
                preg_match('/' . $pattern . '/', $postcode, $matches);
                if (count($matches)) {
                    return true;
                }
            }
            return false;
        } else {
            return true;
        }
    }
}
