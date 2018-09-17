<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\Quote\Address;

use Zend_Validate_Exception;

class Validator extends \Magento\Framework\Validator\AbstractValidator
{
    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryFactory;

    /**
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     */
    public function __construct(
        \Magento\Directory\Model\CountryFactory $countryFactory
    ) {
        $this->countryFactory = $countryFactory;
    }

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  \Magento\Quote\Model\Quote\Address $value
     * @return boolean
     * @throws Zend_Validate_Exception If validation of $value is impossible
     */
    public function isValid($value)
    {
        $messages = [];
        $email = $value->getEmail();
        $validator = new \Zend\Validator\EmailAddress();
        if (!empty($email) && !$validator->isValid($email)) {
            $messages['invalid_email_format'] = 'Invalid email format';
        }

        $countryId = $value->getCountryId();
        if (!empty($countryId)) {
            $country = $this->countryFactory->create();
            $country->load($countryId);
            if (!$country->getId()) {
                $messages['invalid_country_code'] = 'Invalid country code';
            }
        }

        $this->_addMessages($messages);

        return empty($messages);
    }
}
