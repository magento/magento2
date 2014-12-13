<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
     * Validate locale code
     *
     * @param string $localeCode
     * @return bool
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
