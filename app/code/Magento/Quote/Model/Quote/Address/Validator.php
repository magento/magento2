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

        $pattern_name = '/(?:[\p{L}\p{M}\,\-\_\.\'’`\s\d]){1,255}+/u';
        $messages = $this->isValidPattern($pattern_name, $value->getFirstname(), $messages, 'invalid_firstname', 'First Name is not valid!');
        $messages = $this->isValidPattern($pattern_name, $value->getLastname(), $messages, 'invalid_lastname', 'Last Name is not valid!');
        $messages = $this->isValidPattern($pattern_name, $value->getMiddlename(), $messages, 'invalid_middlename', 'Middle Name is not valid!');
        $messages = $this->isValidPattern($pattern_name, $value->getCompany(), $messages, 'invalid_company', 'Company is not valid!');
        $pattern_street = "/(?:[\p{L}\p{M}\"[],-.'’`&\s\d]){1,255}+/u";
        foreach ($value->getStreet() as $street) {
            $messages = $this->isValidPattern($pattern_street, $street, $messages, 'invalid_street', 'Street is not valid!');
        }
        $pattern_city = '/(?:[\p{L}\p{M}\s\-\']{1,100})/u';
        $messages = $this->isValidPattern($pattern_city, $value->getCity(), $messages, 'invalid_city', 'City is not valid!');

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
    /**
     * @param string $pattern
     * @param mixed $value
     * @param array $messages
     * @param $message_type
     * @param $message
     * @return array
     */
    public function isValidPattern(string $pattern, mixed $value, array $messages, $message_type, $message): array
    {
        if ($value == null) {
            return $messages;
        }
        if (preg_match($pattern, $value, $matches)) {
            if ($matches[0] != $value) {
                $messages[$message_type] = $message;
            }
        }
        return $messages;
    }
}
