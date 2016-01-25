<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Customer helper for config values.
 */
class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Configuration path to customer password minimum length
     */
    const XML_PATH_MINIMUM_PASSWORD_LENGTH = 'customer/password/minimum_password_length';

    /**
     * Configuration path to customer password required character classes number
     */
    const XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER = 'customer/password/required_character_classes_number';

    /**
     * Get Email of a customer service
     *
     * @return string
     */
    public function getMinimumPasswordLength()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MINIMUM_PASSWORD_LENGTH,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }

    /**
     * Get Email of a customer service
     *
     * @return string
     */
    public function getRequiredCharacterClassesNumber()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }
}
