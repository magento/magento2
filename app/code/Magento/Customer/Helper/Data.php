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
 * @category    Magento
 * @package     Magento_Customer
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Customer Data Helper
 *
 * @category   Magento
 * @package    Magento_Customer
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Helper;

class Data extends \Magento\Core\Helper\AbstractHelper
{
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
     * VAT class constants
     */
    const VAT_CLASS_DOMESTIC    = 'domestic';
    const VAT_CLASS_INTRA_UNION = 'intra_union';
    const VAT_CLASS_INVALID     = 'invalid';
    const VAT_CLASS_ERROR       = 'error';

    /**
     * Customer groups collection
     *
     * @var \Magento\Customer\Model\Resource\Group\Collection
     */
    protected $_groups;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData = null;

    /**
     * Customer address
     *
     * @var \Magento\Customer\Helper\Address
     */
    protected $_customerAddress = null;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @var \Magento\Core\Model\Config
     */
    protected $_coreConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    
    /**
     * @var \Magento\Customer\Model\GroupFactory
     */
    protected $_groupFactory;

    /**
     * @var \Magento\Customer\Model\FormFactory
     */
    protected $_formFactory;

    /**
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Customer\Helper\Address $customerAddress
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Helper\Context $context
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Core\Model\Config $coreConfig
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\GroupFactory $groupFactory
     * @param \Magento\Customer\Model\FormFactory $formFactory
     */
    public function __construct(
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Customer\Helper\Address $customerAddress,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Helper\Context $context,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Core\Model\Config $coreConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\GroupFactory $groupFactory,
        \Magento\Customer\Model\FormFactory $formFactory
    ) {
        $this->_eventManager = $eventManager;
        $this->_customerAddress = $customerAddress;
        $this->_coreData = $coreData;
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_coreConfig = $coreConfig;
        $this->_customerSession = $customerSession;
        $this->_groupFactory = $groupFactory;
        $this->_formFactory = $formFactory;
        parent::__construct($context);
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

    /**
     * Retrieve logged in customer
     *
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        if (empty($this->_customer)) {
            $this->_customer = $this->_customerSession->getCustomer();
        }
        return $this->_customer;
    }

    /**
     * Retrieve customer groups collection
     *
     * @return \Magento\Customer\Model\Resource\Group\Collection
     */
    public function getGroups()
    {
        if (empty($this->_groups)) {
            $this->_groups = $this->_createGroup()->getResourceCollection()
                ->setRealGroupsFilter()
                ->load();
        }
        return $this->_groups;
    }

    /**
     * Retrieve current (logged in) customer object
     *
     * @return \Magento\Customer\Model\Customer
     */
    public function getCurrentCustomer()
    {
        return $this->getCustomer();
    }

    /**
     * Retrieve current customer name
     *
     * @return string
     */
    public function getCustomerName()
    {
        return $this->getCustomer()->getName();
    }

    /**
     * Check customer has address
     *
     * @return bool
     */
    public function customerHasAddresses()
    {
        return count($this->getCustomer()->getAddresses()) > 0;
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

        if (!$referer && !$this->_coreStoreConfig->getConfigFlag(self::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD)
            && !$this->_customerSession->getNoReferer()
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
     * Check is confirmation required
     *
     * @return bool
     */
    public function isConfirmationRequired()
    {
        return $this->getCustomer()->isConfirmationRequired();
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
     * @return array|bool
     */
    public function getNamePrefixOptions($store = null)
    {
        return $this->_prepareNamePrefixSuffixOptions(
            $this->_customerAddress->getConfig('prefix_options', $store)
        );
    }

    /**
     * Retrieve name suffix dropdown options
     *
     * @return array|bool
     */
    public function getNameSuffixOptions($store = null)
    {
        return $this->_prepareNamePrefixSuffixOptions(
            $this->_customerAddress->getConfig('suffix_options', $store)
        );
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
            $value = $this->escapeHtml(trim($value));
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
        return $this->_coreData->uniqHash();
    }

    /**
     * Retrieve customer reset password link expiration period in days
     *
     * @return int
     */
    public function getResetPasswordLinkExpirationPeriod()
    {
        return (int) $this->_coreConfig->getValue(
            self::XML_PATH_CUSTOMER_RESET_PASSWORD_LINK_EXPIRATION_PERIOD,
            'default'
        );
    }

    /**
     * Get default customer group id
     *
     * @param \Magento\Core\Model\Store|string|int $store
     * @return int
     */
    public function getDefaultCustomerGroupId($store = null)
    {
        return (int)$this->_coreStoreConfig->getConfig(\Magento\Customer\Model\Group::XML_PATH_DEFAULT_ID, $store);
    }

    /**
     * Retrieve customer group ID based on his VAT number
     *
     * @param string $customerCountryCode
     * @param \Magento\Object $vatValidationResult
     * @param \Magento\Core\Model\Store|string|int $store
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
            $groupId = (int)$this->_coreStoreConfig->getConfig($vatClassToGroupXmlPathMap[$vatClass], $store);
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
     * @return \Magento\Object
     */
    public function checkVatNumber($countryCode, $vatNumber, $requesterCountryCode = '', $requesterVatNumber = '')
    {
        // Default response
        $gatewayResponse = new \Magento\Object(array(
            'is_valid' => false,
            'request_date' => '',
            'request_identifier' => '',
            'request_success' => false
        ));

        if (!extension_loaded('soap')) {
            $this->_logger->logException(new \Magento\Core\Exception(__('PHP SOAP extension is required.')));
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

            $gatewayResponse->setIsValid((boolean) $result->valid);
            $gatewayResponse->setRequestDate((string) $result->requestDate);
            $gatewayResponse->setRequestIdentifier((string) $result->requestIdentifier);
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
     */
    public function canCheckVatNumber($countryCode, $vatNumber, $requesterCountryCode, $requesterVatNumber)
    {
        $result = true;
        /** @var $coreHelper \Magento\Core\Helper\Data */
        $coreHelper = $this->_coreData;

        if (!is_string($countryCode)
            || !is_string($vatNumber)
            || !is_string($requesterCountryCode)
            || !is_string($requesterVatNumber)
            || empty($countryCode)
            || !$coreHelper->isCountryInEU($countryCode)
            || empty($vatNumber)
            || (empty($requesterCountryCode) && !empty($requesterVatNumber))
            || (!empty($requesterCountryCode) && empty($requesterVatNumber))
            || (!empty($requesterCountryCode) && !$coreHelper->isCountryInEU($requesterCountryCode))
        ) {
            $result = false;
        }

        return $result;
    }

    /**
     * Get VAT class
     *
     * @param string $customerCountryCode
     * @param \Magento\Object $vatValidationResult
     * @param \Magento\Core\Model\Store|string|int|null $store
     * @return null|string
     */
    public function getCustomerVatClass($customerCountryCode, $vatValidationResult, $store = null)
    {
        $vatClass = null;

        $isVatNumberValid = $vatValidationResult->getIsValid();

        if (is_string($customerCountryCode)
            && !empty($customerCountryCode)
            && $customerCountryCode === $this->_coreData->getMerchantCountryCode($store)
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
     * Get validation message that will be displayed to user by VAT validation result object
     *
     * @param \Magento\Customer\Model\Address $customerAddress
     * @param bool $customerGroupAutoAssignDisabled
     * @param \Magento\Object $validationResult
     * @return \Magento\Object
     */
    public function getVatValidationUserMessage($customerAddress, $customerGroupAutoAssignDisabled, $validationResult)
    {
        $message = '';
        $isError = true;
        $customerVatClass = $this->getCustomerVatClass($customerAddress->getCountryId(), $validationResult);
        $groupAutoAssignDisabled = $this->_coreStoreConfig->getConfigFlag(self::XML_PATH_CUSTOMER_VIV_GROUP_AUTO_ASSIGN);

        $willChargeTaxMessage    = __('You will be charged tax.');
        $willNotChargeTaxMessage = __('You will not be charged tax.');

        if ($validationResult->getIsValid()) {
            $message = __('Your VAT ID was successfully validated.');
            $isError = false;

            if (!$groupAutoAssignDisabled && !$customerGroupAutoAssignDisabled) {
                $message .= ' ' . ($customerVatClass == self::VAT_CLASS_DOMESTIC
                    ? $willChargeTaxMessage
                    : $willNotChargeTaxMessage);
            }
        } else if ($validationResult->getRequestSuccess()) {
            $message = sprintf(
                __('The VAT ID entered (%s) is not a valid VAT ID.') . ' ',
                $this->escapeHtml($customerAddress->getVatId())
            );
            if (!$groupAutoAssignDisabled && !$customerGroupAutoAssignDisabled) {
                $message .= $willChargeTaxMessage;
            }
        }
        else {
            $contactUsMessage = sprintf(__('If you believe this is an error, please contact us at %s'),
                $this->_coreStoreConfig->getConfig(self::XML_PATH_SUPPORT_EMAIL));

            $message = __('Your Tax ID cannot be validated.') . ' '
                . (!$groupAutoAssignDisabled && !$customerGroupAutoAssignDisabled
                    ? $willChargeTaxMessage . ' ' : '')
                . $contactUsMessage;
        }

        $validationMessageEnvelope = new \Magento\Object();
        $validationMessageEnvelope->setMessage($message);
        $validationMessageEnvelope->setIsError($isError);

        return $validationMessageEnvelope;
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
     * @param \Magento\App\RequestInterface $request
     * @param string $formCode The code of EAV form to take the list of attributes from
     * @param \Magento\Core\Model\AbstractModel $entity entity model for the form
     * @param array $additionalAttributes The list of attribute codes to skip filtration for
     * @param string $scope scope of the request
     * @param \Magento\Eav\Model\Form|null $eavForm EAV form model to use for extraction
     * @return array Filtered customer data
     */
    public function extractCustomerData(\Magento\App\RequestInterface $request, $formCode, $entity,
        $additionalAttributes = array(), $scope = null, $eavForm = null
    ) {
        if (is_null($eavForm)) {
            $eavForm = $this->_createForm();
        }
        /** @var \Magento\Eav\Model\Form $eavForm */
        $eavForm->setEntity($entity)
            ->setFormCode($formCode)
            ->ignoreInvisible(false);
        $filteredData = $eavForm->extractData($request, $scope);
        $requestData = $request->getPost($scope);
        foreach ($additionalAttributes as $attributeCode) {
            $filteredData[$attributeCode] = isset($requestData[$attributeCode])
                ? $requestData[$attributeCode] : false;
        }

        $formAttributes = $eavForm->getAttributes();
        /** @var \Magento\Customer\Model\Attribute $attribute */
        foreach ($formAttributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $frontendInput = $attribute->getFrontendInput();
            if ($frontendInput != 'boolean' && $filteredData[$attributeCode] === false) {
                unset($filteredData[$attributeCode]);
            }
        }

        return $filteredData;
    }

    /**
     * @return \Magento\Customer\Model\Group
     */
    protected function _createGroup()
    {
        return $this->_groupFactory->create();
    }

    /**
     * @return \Magento\Customer\Model\Form
     */
    protected function _createForm()
    {
        return $this->_formFactory->create();
    }
}
