<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\Quote\Address;

use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Framework\Validator\EmailAddress;
use Magento\Framework\Validator\ValidateException;
use Magento\Framework\Validator\ValidatorChain;
use Magento\Quote\Model\Quote\Address;

class Validator extends AbstractValidator
{
    /**
     * @var CountryFactory
     */
    protected $countryFactory;

    /**
     * @param CountryFactory $countryFactory
     */
    public function __construct(
        CountryFactory $countryFactory
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
     * @param  Address $value
     * @return boolean
     * @throws ValidateException If validation of $value is impossible
     */
    public function isValid($value)
    {
        $messages = [];
        $email = $value->getEmail();
        if (!empty($email) && !ValidatorChain::is($email, EmailAddress::class)) {
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
