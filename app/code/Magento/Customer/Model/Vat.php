<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;
use Magento\Store\Model\Information as StoreInformation;
use Psr\Log\LoggerInterface as PsrLogger;
use Magento\Store\Model\ScopeInterface;

/**
 * Customer VAT model
 */
class Vat
{
    /**
     * Config paths to VAT related customer groups
     */
    const XML_PATH_CUSTOMER_VIV_INTRA_UNION_GROUP = 'customer/create_account/viv_intra_union_group';

    const XML_PATH_CUSTOMER_VIV_DOMESTIC_GROUP = 'customer/create_account/viv_domestic_group';

    const XML_PATH_CUSTOMER_VIV_INVALID_GROUP = 'customer/create_account/viv_invalid_group';

    const XML_PATH_CUSTOMER_VIV_ERROR_GROUP = 'customer/create_account/viv_error_group';

    /**
     * VAT class constants
     */
    const VAT_CLASS_DOMESTIC = 'domestic';

    const VAT_CLASS_INTRA_UNION = 'intra_union';

    const VAT_CLASS_INVALID = 'invalid';

    const VAT_CLASS_ERROR = 'error';

    /**
     * WSDL of VAT validation service
     *
     */
    const VAT_VALIDATION_WSDL_URL = 'https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';

    /**
     * Config path to option that enables/disables automatic group assignment based on VAT
     */
    const XML_PATH_CUSTOMER_GROUP_AUTO_ASSIGN = 'customer/create_account/auto_group_assign';

    /**
     * Config path to UE country list
     */
    const XML_PATH_EU_COUNTRIES_LIST = 'general/country/eu_countries';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var PsrLogger
     */
    protected $logger;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param PsrLogger $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        PsrLogger $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * Retrieve merchant country code
     *
     * @param Store|string|int|null $store
     * @return string
     */
    public function getMerchantCountryCode($store = null)
    {
        return (string)$this->scopeConfig->getValue(
            StoreInformation::XML_PATH_STORE_INFO_COUNTRY_CODE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve merchant VAT number
     *
     * @param Store|string|int|null $store
     * @return string
     */
    public function getMerchantVatNumber($store = null)
    {
        return (string)$this->scopeConfig->getValue(
            StoreInformation::XML_PATH_STORE_INFO_VAT_NUMBER,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve customer group ID based on his VAT number
     *
     * @param string $customerCountryCode
     * @param DataObject $vatValidationResult
     * @param \Magento\Store\Model\Store|string|int $store
     * @return null|int
     */
    public function getCustomerGroupIdBasedOnVatNumber($customerCountryCode, $vatValidationResult, $store = null)
    {
        $groupId = null;

        $isAutoGroupAssign = $this->scopeConfig->isSetFlag(
            self::XML_PATH_CUSTOMER_GROUP_AUTO_ASSIGN,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        if (!$isAutoGroupAssign) {
            return $groupId;
        }

        $vatClass = $this->getCustomerVatClass($customerCountryCode, $vatValidationResult, $store);

        $vatClassToGroupXmlPathMap = [
            self::VAT_CLASS_DOMESTIC => self::XML_PATH_CUSTOMER_VIV_DOMESTIC_GROUP,
            self::VAT_CLASS_INTRA_UNION => self::XML_PATH_CUSTOMER_VIV_INTRA_UNION_GROUP,
            self::VAT_CLASS_INVALID => self::XML_PATH_CUSTOMER_VIV_INVALID_GROUP,
            self::VAT_CLASS_ERROR => self::XML_PATH_CUSTOMER_VIV_ERROR_GROUP,
        ];

        if (isset($vatClassToGroupXmlPathMap[$vatClass])) {
            $groupId = (int)$this->scopeConfig->getValue(
                $vatClassToGroupXmlPathMap[$vatClass],
                ScopeInterface::SCOPE_STORE,
                $store
            );
        }

        return $groupId;
    }

    /**
     * Send request to VAT validation service and return validation result
     *
     * @param string $countryCode
     * @param string $vatNumber
     * @param string $requesterCountryCode
     * @param string $requesterVatNumber
     *
     * @return DataObject
     */
    public function checkVatNumber($countryCode, $vatNumber, $requesterCountryCode = '', $requesterVatNumber = '')
    {
        // Default response
        $gatewayResponse = new DataObject([
            'is_valid' => false,
            'request_date' => '',
            'request_identifier' => '',
            'request_success' => false,
            'request_message' => __('Error during VAT Number verification.'),
        ]);

        if (!extension_loaded('soap')) {
            $this->logger->critical(new LocalizedException(__('PHP SOAP extension is required.')));
            return $gatewayResponse;
        }

        if (!$this->canCheckVatNumber($countryCode, $vatNumber, $requesterCountryCode, $requesterVatNumber)) {
            return $gatewayResponse;
        }

        $countryCodeForVatNumber = $this->getCountryCodeForVatNumber($countryCode);
        $requesterCountryCodeForVatNumber = $this->getCountryCodeForVatNumber($requesterCountryCode);

        try {
            $soapClient = $this->createVatNumberValidationSoapClient();

            $requestParams = [];
            $requestParams['countryCode'] = $countryCodeForVatNumber;
            $vatNumberSanitized = $this->isCountryInEU($countryCode)
                ? str_replace([' ', '-', $countryCodeForVatNumber], ['', '', ''], $vatNumber)
                : str_replace([' ', '-'], ['', ''], $vatNumber);
            $requestParams['vatNumber'] = $vatNumberSanitized;
            $requestParams['requesterCountryCode'] = $requesterCountryCodeForVatNumber;
            $reqVatNumSanitized = $this->isCountryInEU($requesterCountryCode)
                ? str_replace([' ', '-', $requesterCountryCodeForVatNumber], ['', '', ''], $requesterVatNumber)
                : str_replace([' ', '-'], ['', ''], $requesterVatNumber);
            $requestParams['requesterVatNumber'] = $reqVatNumSanitized;
            // Send request to service
            $result = $soapClient->checkVatApprox($requestParams);

            $gatewayResponse->setIsValid((bool)$result->valid);
            $gatewayResponse->setRequestDate((string)$result->requestDate);
            $gatewayResponse->setRequestIdentifier((string)$result->requestIdentifier);
            $gatewayResponse->setRequestSuccess(true);

            if ($gatewayResponse->getIsValid()) {
                $gatewayResponse->setRequestMessage(__('VAT Number is valid.'));
            } else {
                $gatewayResponse->setRequestMessage(__('Please enter a valid VAT number.'));
            }
        } catch (\Exception $exception) {
            $gatewayResponse->setIsValid(false);
            $gatewayResponse->setRequestDate('');
            $gatewayResponse->setRequestIdentifier('');
        }

        return $gatewayResponse;
    }

    /**
     * Create SOAP client based on VAT validation service WSDL
     *
     * @param boolean $trace
     * @return \SoapClient
     */
    protected function createVatNumberValidationSoapClient($trace = false)
    {
        return new \SoapClient(self::VAT_VALIDATION_WSDL_URL, ['trace' => $trace]);
    }

    /**
     * Check if parameters are valid to send to VAT validation service
     *
     * @param string $countryCode
     * @param string $vatNumber
     * @param string $requesterCountryCode
     * @param string $requesterVatNumber
     *
     * @return boolean
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function canCheckVatNumber($countryCode, $vatNumber, $requesterCountryCode, $requesterVatNumber)
    {
        return !(!is_string($countryCode)
            || !is_string($vatNumber)
            || !is_string($requesterCountryCode)
            || !is_string($requesterVatNumber)
            || empty($countryCode)
            || !$this->isCountryInEU($countryCode)
            || empty($vatNumber)
            || empty($requesterCountryCode) && !empty($requesterVatNumber)
            || !empty($requesterCountryCode) && empty($requesterVatNumber)
            || !empty($requesterCountryCode) && !$this->isCountryInEU($requesterCountryCode)
        );
    }

    /**
     * Get VAT class
     *
     * @param string $customerCountryCode
     * @param DataObject $vatValidationResult
     * @param Store|string|int|null $store
     * @return null|string
     */
    public function getCustomerVatClass($customerCountryCode, $vatValidationResult, $store = null)
    {
        $vatClass = null;

        $isVatNumberValid = $vatValidationResult->getIsValid();

        if (is_string($customerCountryCode)
            && !empty($customerCountryCode)
            && $customerCountryCode === $this->getMerchantCountryCode($store)
            && $isVatNumberValid
        ) {
            $vatClass = self::VAT_CLASS_DOMESTIC;
        } elseif ($isVatNumberValid) {
            $vatClass = self::VAT_CLASS_INTRA_UNION;
        } else {
            $vatClass = self::VAT_CLASS_INVALID;
        }

        if (!$vatValidationResult->getRequestSuccess()) {
            $vatClass = self::VAT_CLASS_ERROR;
        }

        return $vatClass;
    }

    /**
     * Check whether specified country is in EU countries list
     *
     * @param string $countryCode
     * @param null|int $storeId
     * @return bool
     */
    public function isCountryInEU($countryCode, $storeId = null)
    {
        $euCountries = explode(
            ',',
            $this->scopeConfig->getValue(self::XML_PATH_EU_COUNTRIES_LIST, ScopeInterface::SCOPE_STORE, $storeId)
        );
        return in_array($countryCode, $euCountries);
    }

    /**
     * Returns the country code to use in the VAT number which is not always the same as the normal country code
     *
     * @param string $countryCode
     * @return string
     */
    private function getCountryCodeForVatNumber(string $countryCode): string
    {
        // Greece uses a different code for VAT numbers then its country code
        // See: http://ec.europa.eu/taxation_customs/vies/faq.html#item_11
        // And https://en.wikipedia.org/wiki/VAT_identification_number:
        // "The full identifier starts with an ISO 3166-1 alpha-2 (2 letters) country code
        // (except for Greece, which uses the ISO 639-1 language code EL for the Greek language,
        // instead of its ISO 3166-1 alpha-2 country code GR)"

        return $countryCode === 'GR' ? 'EL' : $countryCode;
    }
}
