<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Helper;

/**
 * Customer Data Helper
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Customer group cache context
     */
    const CONTEXT_GROUP = 'customer_group';

    /**
     * Customer authorization cache context
     */
    const CONTEXT_AUTH = 'customer_logged_in';

    /**
     * Query param name for last url visited
     */
    const REFERER_QUERY_PARAM_NAME = 'referer';

    /**
     * Route for customer account login page
     */
    const ROUTE_ACCOUNT_LOGIN = 'customer/account/login';

    /**
     * Config name for Redirect Customer to Account Dashboard after Logging in setting
     */
    const XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD = 'customer/startup/redirect_dashboard';

    /**
     * Config paths to VAT related customer groups
     */
    const XML_PATH_CUSTOMER_VIV_INTRA_UNION_GROUP = 'customer/create_account/viv_intra_union_group';

    const XML_PATH_CUSTOMER_VIV_DOMESTIC_GROUP = 'customer/create_account/viv_domestic_group';

    const XML_PATH_CUSTOMER_VIV_INVALID_GROUP = 'customer/create_account/viv_invalid_group';

    const XML_PATH_CUSTOMER_VIV_ERROR_GROUP = 'customer/create_account/viv_error_group';

    /**
     * Config path to option that enables/disables automatic group assignment based on VAT
     */
    const XML_PATH_CUSTOMER_VIV_GROUP_AUTO_ASSIGN = 'customer/create_account/viv_disable_auto_group_assign_default';

    /**
     * Config path to support email
     */
    const XML_PATH_SUPPORT_EMAIL = 'trans_email/ident_support/email';

    /**
     * WSDL of VAT validation service
     *
     */
    const VAT_VALIDATION_WSDL_URL = 'http://ec.europa.eu/taxation_customs/vies/services/checkVatService?wsdl';

    /**
     * Configuration path to expiration period of reset password link
     */
    const XML_PATH_CUSTOMER_RESET_PASSWORD_LINK_EXPIRATION_PERIOD = 'customer/password/reset_link_expiration_period';

    /**
     * Configuration path to merchant country id
     */
    const XML_PATH_MERCHANT_COUNTRY_CODE = 'general/store_information/country_id';

    /**
     * Config path to merchant VAT number
     */
    const XML_PATH_MERCHANT_VAT_NUMBER = 'general/store_information/merchant_vat_number';

    /**
     * Config path to UE country list
     */
    const XML_PATH_EU_COUNTRIES_LIST = 'general/country/eu_countries';

    /**
     * VAT class constants
     */
    const VAT_CLASS_DOMESTIC = 'domestic';

    const VAT_CLASS_INTRA_UNION = 'intra_union';

    const VAT_CLASS_INVALID = 'invalid';

    const VAT_CLASS_ERROR = 'error';

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * Customer address
     *
     * @var Address
     */
    protected $_customerAddress = null;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Service\V1\CustomerGroupServiceInterface
     */
    protected $_groupService;

    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    protected $_formFactory;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Address $customerAddress
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Service\V1\CustomerGroupServiceInterface $groupService
     * @param \Magento\Customer\Model\Metadata\FormFactory $formFactory
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Math\Random $mathRandom
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Address $customerAddress,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Service\V1\CustomerGroupServiceInterface $groupService,
        \Magento\Customer\Model\Metadata\FormFactory $formFactory,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Math\Random $mathRandom
    ) {
        $this->_customerAddress = $customerAddress;
        $this->_coreData = $coreData;
        $this->_scopeConfig = $scopeConfig;
        $this->_customerSession = $customerSession;
        $this->_groupService = $groupService;
        $this->_formFactory = $formFactory;
        $this->_escaper = $escaper;
        $this->mathRandom = $mathRandom;
        parent::__construct($context);
    }

    /**
     * Retrieve merchant country code
     *
     * @param \Magento\Store\Model\Store|string|int|null $store
     * @return string
     */
    public function getMerchantCountryCode($store = null)
    {
        return (string)$this->_scopeConfig->getValue(
            self::XML_PATH_MERCHANT_COUNTRY_CODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve merchant VAT number
     *
     * @param \Magento\Store\Model\Store|string|int|null $store
     * @return string
     */
    public function getMerchantVatNumber($store = null)
    {
        return (string)$this->_scopeConfig->getValue(
            self::XML_PATH_MERCHANT_VAT_NUMBER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
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
            $this->_scopeConfig->getValue(
                self::XML_PATH_EU_COUNTRIES_LIST,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );
        return in_array($countryCode, $euCountries);
    }

    /**
     * Check customer is logged in
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->_customerSession->isLoggedIn();
    }

    /**************************************************************************
     * Customer urls
     */

    /**
     * Retrieve customer login url
     *
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->_getUrl(self::ROUTE_ACCOUNT_LOGIN, $this->getLoginUrlParams());
    }

    /**
     * Retrieve parameters of customer login url
     *
     * @return array
     */
    public function getLoginUrlParams()
    {
        $params = array();

        $referer = $this->_getRequest()->getParam(self::REFERER_QUERY_PARAM_NAME);

        if (!$referer && !$this->_scopeConfig->isSetFlag(
            self::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) && !$this->_customerSession->getNoReferer()
        ) {
            $referer = $this->_getUrl('*/*/*', array('_current' => true, '_use_rewrite' => true));
            $referer = $this->_coreData->urlEncode($referer);
        }

        if ($referer) {
            $params = array(self::REFERER_QUERY_PARAM_NAME => $referer);
        }

        return $params;
    }

    /**
     * Retrieve customer login POST URL
     *
     * @return string
     */
    public function getLoginPostUrl()
    {
        $params = array();
        if ($this->_getRequest()->getParam(self::REFERER_QUERY_PARAM_NAME)) {
            $params = array(
                self::REFERER_QUERY_PARAM_NAME => $this->_getRequest()->getParam(self::REFERER_QUERY_PARAM_NAME)
            );
        }
        return $this->_getUrl('customer/account/loginPost', $params);
    }

    /**
     * Retrieve customer logout url
     *
     * @return string
     */
    public function getLogoutUrl()
    {
        return $this->_getUrl('customer/account/logout');
    }

    /**
     * Retrieve customer dashboard url
     *
     * @return string
     */
    public function getDashboardUrl()
    {
        return $this->_getUrl('customer/account');
    }

    /**
     * Retrieve customer account page url
     *
     * @return string
     */
    public function getAccountUrl()
    {
        return $this->_getUrl('customer/account');
    }

    /**
     * Retrieve customer register form url
     *
     * @return string
     */
    public function getRegisterUrl()
    {
        return $this->_getUrl('customer/account/create');
    }

    /**
     * Retrieve customer register form post url
     *
     * @return string
     */
    public function getRegisterPostUrl()
    {
        return $this->_getUrl('customer/account/createpost');
    }

    /**
     * Retrieve customer account edit form url
     *
     * @return string
     */
    public function getEditUrl()
    {
        return $this->_getUrl('customer/account/edit');
    }

    /**
     * Retrieve customer edit POST URL
     *
     * @return string
     */
    public function getEditPostUrl()
    {
        return $this->_getUrl('customer/account/editpost');
    }

    /**
     * Retrieve url of forgot password page
     *
     * @return string
     */
    public function getForgotPasswordUrl()
    {
        return $this->_getUrl('customer/account/forgotpassword');
    }

    /**
     * Retrieve confirmation URL for Email
     *
     * @param string $email
     * @return string
     */
    public function getEmailConfirmationUrl($email = null)
    {
        return $this->_getUrl('customer/account/confirmation', array('email' => $email));
    }

    /**
     * Check whether customers registration is allowed
     *
     * @return bool
     */
    public function isRegistrationAllowed()
    {
        return true;
    }

    /**
     * Retrieve name prefix dropdown options
     *
     * @param null $store
     * @return array|bool
     */
    public function getNamePrefixOptions($store = null)
    {
        return $this->_prepareNamePrefixSuffixOptions($this->_customerAddress->getConfig('prefix_options', $store));
    }

    /**
     * Retrieve name suffix dropdown options
     *
     * @param null $store
     * @return array|bool
     */
    public function getNameSuffixOptions($store = null)
    {
        return $this->_prepareNamePrefixSuffixOptions($this->_customerAddress->getConfig('suffix_options', $store));
    }

    /**
     * Unserialize and clear name prefix or suffix options
     *
     * @param string $options
     * @return array|bool
     */
    protected function _prepareNamePrefixSuffixOptions($options)
    {
        $options = trim($options);
        if (empty($options)) {
            return false;
        }
        $result = array();
        $options = explode(';', $options);
        foreach ($options as $value) {
            $value = $this->_escaper->escapeHtml(trim($value));
            $result[$value] = $value;
        }
        return $result;
    }

    /**
     * Generate unique token for reset password confirmation link
     *
     * @return string
     */
    public function generateResetPasswordLinkToken()
    {
        return $this->mathRandom->getUniqueHash();
    }

    /**
     * Retrieve customer reset password link expiration period in days
     *
     * @return int
     */
    public function getResetPasswordLinkExpirationPeriod()
    {
        return (int)$this->_scopeConfig->getValue(
            self::XML_PATH_CUSTOMER_RESET_PASSWORD_LINK_EXPIRATION_PERIOD,
            \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT
        );
    }

    /**
     * Get default customer group id
     *
     * @param \Magento\Store\Model\Store|string|int $store
     * @return int
     */
    public function getDefaultCustomerGroupId($store = null)
    {
        return $this->_groupService->getDefaultGroup($store)->getId();
    }

    /**
     * Retrieve customer group ID based on his VAT number
     *
     * @param string $customerCountryCode
     * @param \Magento\Framework\Object $vatValidationResult
     * @param \Magento\Store\Model\Store|string|int $store
     * @return null|int
     */
    public function getCustomerGroupIdBasedOnVatNumber($customerCountryCode, $vatValidationResult, $store = null)
    {
        $groupId = null;

        $vatClass = $this->getCustomerVatClass($customerCountryCode, $vatValidationResult, $store);

        $vatClassToGroupXmlPathMap = array(
            self::VAT_CLASS_DOMESTIC => self::XML_PATH_CUSTOMER_VIV_DOMESTIC_GROUP,
            self::VAT_CLASS_INTRA_UNION => self::XML_PATH_CUSTOMER_VIV_INTRA_UNION_GROUP,
            self::VAT_CLASS_INVALID => self::XML_PATH_CUSTOMER_VIV_INVALID_GROUP,
            self::VAT_CLASS_ERROR => self::XML_PATH_CUSTOMER_VIV_ERROR_GROUP
        );

        if (isset($vatClassToGroupXmlPathMap[$vatClass])) {
            $groupId = (int)$this->_scopeConfig->getValue(
                $vatClassToGroupXmlPathMap[$vatClass],
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
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
     * @return \Magento\Framework\Object
     */
    public function checkVatNumber($countryCode, $vatNumber, $requesterCountryCode = '', $requesterVatNumber = '')
    {
        // Default response
        $gatewayResponse = new \Magento\Framework\Object(
            array('is_valid' => false, 'request_date' => '', 'request_identifier' => '', 'request_success' => false)
        );

        if (!extension_loaded('soap')) {
            $this->_logger->logException(new \Magento\Framework\Model\Exception(__('PHP SOAP extension is required.')));
            return $gatewayResponse;
        }

        if (!$this->canCheckVatNumber($countryCode, $vatNumber, $requesterCountryCode, $requesterVatNumber)) {
            return $gatewayResponse;
        }

        try {
            $soapClient = $this->_createVatNumberValidationSoapClient();

            $requestParams = array();
            $requestParams['countryCode'] = $countryCode;
            $requestParams['vatNumber'] = str_replace(array(' ', '-'), array('', ''), $vatNumber);
            $requestParams['requesterCountryCode'] = $requesterCountryCode;
            $requestParams['requesterVatNumber'] = str_replace(array(' ', '-'), array('', ''), $requesterVatNumber);

            // Send request to service
            $result = $soapClient->checkVatApprox($requestParams);

            $gatewayResponse->setIsValid((bool)$result->valid);
            $gatewayResponse->setRequestDate((string)$result->requestDate);
            $gatewayResponse->setRequestIdentifier((string)$result->requestIdentifier);
            $gatewayResponse->setRequestSuccess(true);
        } catch (\Exception $exception) {
            $gatewayResponse->setIsValid(false);
            $gatewayResponse->setRequestDate('');
            $gatewayResponse->setRequestIdentifier('');
        }

        return $gatewayResponse;
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
        $result = true;
        if (!is_string(
            $countryCode
        ) || !is_string(
            $vatNumber
        ) || !is_string(
            $requesterCountryCode
        ) || !is_string(
            $requesterVatNumber
        ) || empty($countryCode) || !$this->isCountryInEU(
            $countryCode
        ) ||
            empty($vatNumber) ||
            empty($requesterCountryCode) && !empty($requesterVatNumber) ||
            !empty($requesterCountryCode) && empty($requesterVatNumber) ||
            !empty($requesterCountryCode) && !$this->isCountryInEU($requesterCountryCode)
        ) {
            $result = false;
        }

        return $result;
    }

    /**
     * Get VAT class
     *
     * @param string $customerCountryCode
     * @param \Magento\Framework\Object $vatValidationResult
     * @param \Magento\Store\Model\Store|string|int|null $store
     * @return null|string
     */
    public function getCustomerVatClass($customerCountryCode, $vatValidationResult, $store = null)
    {
        $vatClass = null;

        $isVatNumberValid = $vatValidationResult->getIsValid();

        if (is_string(
            $customerCountryCode
        ) && !empty($customerCountryCode) && $customerCountryCode === $this->getMerchantCountryCode(
            $store
        ) && $isVatNumberValid
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
     * Create SOAP client based on VAT validation service WSDL
     *
     * @param boolean $trace
     * @return \SoapClient
     */
    protected function _createVatNumberValidationSoapClient($trace = false)
    {
        return new \SoapClient(self::VAT_VALIDATION_WSDL_URL, array('trace' => $trace));
    }

    /**
     * Perform customer data filtration based on form code and form object
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string $formCode The code of EAV form to take the list of attributes from
     * @param string $entityType entity type for the form
     * @param string[] $additionalAttributes The list of attribute codes to skip filtration for
     * @param string $scope scope of the request
     * @param \Magento\Customer\Model\Metadata\Form $metadataForm to use for extraction
     * @return array Filtered customer data
     */
    public function extractCustomerData(
        \Magento\Framework\App\RequestInterface $request,
        $formCode,
        $entityType,
        $additionalAttributes = array(),
        $scope = null,
        \Magento\Customer\Model\Metadata\Form $metadataForm = null
    ) {
        if (is_null($metadataForm)) {
            $metadataForm = $this->_formFactory->create(
                $entityType,
                $formCode,
                array(),
                false,
                \Magento\Customer\Model\Metadata\Form::DONT_IGNORE_INVISIBLE
            );
        }
        $filteredData = $metadataForm->extractData($request, $scope);
        $requestData = $request->getPost($scope);
        foreach ($additionalAttributes as $attributeCode) {
            $filteredData[$attributeCode] = isset($requestData[$attributeCode]) ? $requestData[$attributeCode] : false;
        }

        $formAttributes = $metadataForm->getAttributes();
        /** @var \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata $attribute */
        foreach ($formAttributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $frontendInput = $attribute->getFrontendInput();
            if ($frontendInput != 'boolean' && $filteredData[$attributeCode] === false) {
                unset($filteredData[$attributeCode]);
            }
        }

        return $filteredData;
    }
}
