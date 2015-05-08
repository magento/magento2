<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Locale validator model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Locale;

class Validator
{
    /**
     * @var \Magento\Framework\Locale\ConfigInterface
     */
    protected $_localeConfig;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Locale\ConfigInterface $localeConfig
     */
    public function __construct(\Magento\Framework\Locale\ConfigInterface $localeConfig)
    {
        $this->_localeConfig = $localeConfig;
    }

    /**
     * Validate locale code. Code must be in the list of allowed locales.
     *
     * @param string $localeCode
     * @return bool
     *
     * @api
     */
    public function isValid($localeCode)
    {
        $isValid = true;
        $allowedLocaleCodes = $this->_localeConfig->getAllowedLocales();

        if (!$localeCode || !in_array($localeCode, $allowedLocaleCodes)) {
            $isValid = false;
        }

        return $isValid;
    }
}
