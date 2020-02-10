<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Braintree\Gateway\SubjectReader;

/**
 * Class BillingAddressDataBuilder
 */
class BillingAddressDataBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * BillingAddress block name
     */
    private const BILLING_ADDRESS = 'billing';

    /**
     * The customer’s company. 255 character maximum.
     */
    private const COMPANY = 'company';

    /**
     * The first name value must be less than or equal to 255 characters.
     */
    private const FIRST_NAME = 'firstName';

    /**
     * The last name value must be less than or equal to 255 characters.
     */
    private const LAST_NAME = 'lastName';

    /**
     * The street address. Maximum 255 characters, and must contain at least 1 digit.
     * Required when AVS rules are configured to require street address.
     */
    private const STREET_ADDRESS = 'streetAddress';

    /**
     * The postal code. Postal code must be a string of 5 or 9 alphanumeric digits,
     * optionally separated by a dash or a space. Spaces, hyphens,
     * and all other special characters are ignored.
     */
    private const POSTAL_CODE = 'postalCode';

    /**
     * The ISO 3166-1 alpha-2 country code specified in an address.
     * The gateway only accepts specific alpha-2 values.
     *
     * @link https://developers.braintreepayments.com/reference/general/countries/php#list-of-countries
     */
    private const COUNTRY_CODE = 'countryCodeAlpha2';

    /**
     * The extended address information—such as apartment or suite number. 255 character maximum.
     */
    private const EXTENDED_ADDRESS = 'extendedAddress';

    /**
     * The locality/city. 255 character maximum.
     */
    private const LOCALITY = 'locality';

    /**
     * The state or province. For PayPal addresses, the region must be a 2-letter abbreviation;
     */
    private const REGION = 'region';

    /**
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $result = [];
        $order = $paymentDO->getOrder();

        $billingAddress = $order->getBillingAddress();
        if ($billingAddress) {
            $result[self::BILLING_ADDRESS] = [
                self::REGION => $billingAddress->getRegionCode(),
                self::POSTAL_CODE => $billingAddress->getPostcode(),
                self::COUNTRY_CODE => $billingAddress->getCountryId(),
                self::FIRST_NAME => $billingAddress->getFirstname(),
                self::STREET_ADDRESS => $billingAddress->getStreetLine1(),
                self::LAST_NAME => $billingAddress->getLastname(),
                self::COMPANY => $billingAddress->getCompany(),
                self::EXTENDED_ADDRESS => $billingAddress->getStreetLine2(),
                self::LOCALITY => $billingAddress->getCity()
            ];
        }

        return $result;
    }
}
