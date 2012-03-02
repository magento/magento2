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
 * @category    Mage
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Xmlconnect Application model
 *
 * @method Mage_XmlConnect_Model_Resource_Application _getResource()
 * @method Mage_XmlConnect_Model_Resource_Application getResource()
 * @method string getName()
 * @method Mage_XmlConnect_Model_Application setName(string $value)
 * @method string getCode()
 * @method Mage_XmlConnect_Model_Application setCode(string $value)
 * @method string getType()
 * @method Mage_XmlConnect_Model_Application setType(string $value)
 * @method Mage_XmlConnect_Model_Application setStoreId(int $value)
 * @method string getActiveFrom()
 * @method Mage_XmlConnect_Model_Application setActiveFrom(string $value)
 * @method string getActiveTo()
 * @method Mage_XmlConnect_Model_Application setActiveTo(string $value)
 * @method string getUpdatedAt()
 * @method Mage_XmlConnect_Model_Application setUpdatedAt(string $value)
 * @method int getStatus()
 * @method Mage_XmlConnect_Model_Application setStatus(int $value)
 * @method int getBrowsingMode()
 * @method Mage_XmlConnect_Model_Application setBrowsingMode(int $value)
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Model_Application extends Mage_Core_Model_Abstract
{
    /**
     * Application code cookie name
     */
    const APP_CODE_COOKIE_NAME      = 'app_code';

    /**
     * Device screen size name
     */
    const APP_SCREEN_SIZE_NAME      = 'screen_size';

    /**
     * Device screen size name
     */
    const APP_SCREEN_SIZE_DEFAULT   = '320x480';

    /**
     * Device screen size source name
     */
    const APP_SCREEN_SOURCE_DEFAULT = 'default';

    /**
     * Application status "submitted" value
     */
    const APP_STATUS_SUCCESS    = 1;

    /**
     * Application status "not submitted" value
     */
    const APP_STATUS_INACTIVE   = 0;

    /**
     * Application prefix length of cutted part of deviceType and storeCode
     */
    const APP_PREFIX_CUT_LENGTH = 3;

    /**
     * Last submitted data from history table
     *
     * @var null|array
     */
    protected $_lastParams;

    /**
     * Application submit info
     *
     * @var array
     */
    protected $submit_params = array();

    /**
     * Application submit action type
     *
     * @var bool
     */
    protected $is_resubmit_action = false;

    /**
     * Full application code
     *
     * @var null|string
     */
    protected $code;

    /**
     * Main configuration of current application
     *
     * @deprecated Serialized config storage has been removed
     * @var null|array
     */
    protected $conf;

    /**
     * Configuration model
     *
     * @var Mage_XmlConnect_Model_ConfigData
     */
    protected $_configModel;

    /**
     * Flag of loaded configuration
     *
     * @var bool
     */
    protected $_isConfigurationLoaded = false;

    /**
     * Social networking validation array
     *
     * Social networking validation array specified as
     *      array (
     *          network id => API key string length
     *      )
     *
     * @var array
     */
    protected $_socialNetValidationArray = array(
        Mage_XmlConnect_Helper_Data::SOCIAL_NETWORK_TWITTER,
        Mage_XmlConnect_Helper_Data::SOCIAL_NETWORK_FACEBOOK,
        Mage_XmlConnect_Helper_Data::SOCIAL_NETWORK_LINKEDIN,
    );

    /**
     * Submission/Resubmission key max length
     */
    const APP_MAX_KEY_LENGTH = 40;

    /**
     * XML path to config with an email address
     * for contact to receive credentials
     * of Urban Airship notifications
     */
    const XML_PATH_CONTACT_CREDENTIALS_EMAIL        = 'xmlconnect/mobile_application/urbanairship_credentials_email';

    /**
     * XML path to config with Urban Airship Terms of Service URL
     */
    const XML_PATH_URBAN_AIRSHIP_TOS_URL            = 'xmlconnect/mobile_application/urbanairship_terms_of_service_url';

    /**
     * XML path to config with Urban Airship partner's login URL
     */
    const XML_PATH_URBAN_AIRSHIP_PARTNER_LOGIN_URL  = 'xmlconnect/mobile_application/urbanairship_login_url';

    /**
     * XML path to config with Urban Airship Push notifications product URL
     */
    const XML_PATH_URBAN_AIRSHIP_ABOUT_PUSH_URL     = 'xmlconnect/mobile_application/urbanairship_push_url';

    /**
     * XML path to config with Urban Airship Rich Push notifications product URL
     */
    const XML_PATH_URBAN_AIRSHIP_ABOUT_RICH_PUSH_URL    = 'xmlconnect/mobile_application/urbanairship_rich_push_url';

    /**
     * XML path to config copyright data
     */
    const XML_PATH_DESIGN_FOOTER_COPYRIGHT          = 'design/footer/copyright';

    /**
     * XML path to config restriction status
     * (EE module)
     */
    const XML_PATH_GENERAL_RESTRICTION_IS_ACTIVE    = 'general/restriction/is_active';

    /**
     * XML path to config restriction mode
     * (EE module)
     */
    const XML_PATH_GENERAL_RESTRICTION_MODE         = 'general/restriction/mode';

    /**
     * XML path to config secure base link URL
     */
    const XML_PATH_SECURE_BASE_LINK_URL             = 'web/secure/base_link_url';

    /**
     * XML path to config for paypal business account
     */
    const XML_PATH_PAYPAL_BUSINESS_ACCOUNT          = 'paypal/general/business_account';

    /**
     * XML path to config for default cache time
     */
    const XML_PATH_DEFAULT_CACHE_LIFETIME           = 'xmlconnect/mobile_application/cache_lifetime';

    /**
     * XML path to How-To URL for twitter
     */
    const XML_PATH_HOWTO_TWITTER_URL                = 'xmlconnect/social_networking/howto_twitter_url';

    /**
     * XML path to How-To URL for facebook
     */
    const XML_PATH_HOWTO_FACEBOOK_URL               = 'xmlconnect/social_networking/howto_facebook_url';

    /**
     * XML path to How-To URL for linkedin
     */
    const XML_PATH_HOWTO_LINKEDIN_URL               = 'xmlconnect/social_networking/howto_linkedin_url';

    /**
     * XML path to XmlConnect module version
     */
    const XML_PATH_MODULE_VERSION                   = 'modules/Mage_XmlConnect/innerVersion';

    /**
     * Deprecated config flag
     *
     * @deprecated Serialized config storage has been removed
     */
    const DEPRECATED_CONFIG_FLAG                    = 'deprecated';

    /**
     * Delete on update paths for config data
     *
     * @var array
     */
    protected $_deleteOnUpdateConfig    = array(
        self::DEPRECATED_CONFIG_FLAG => 'native/pages'
    );

    /**
     * Initialize application
     *
     * @return null
     */
    protected function _construct()
    {
        $this->_init('Mage_XmlConnect_Model_Resource_Application');
        $this->_configModel = Mage::getModel('Mage_XmlConnect_Model_ConfigData');
        $this->_configModel->setDeleteOnUpdate($this->getDeleteOnUpdateConfig());
    }

    /**
     * Checks is it app is submitted
     * (edit is premitted only before submission)
     *
     * @return bool
     */
    public function getIsSubmitted()
    {
        return $this->getStatus() == Mage_XmlConnect_Model_Application::APP_STATUS_SUCCESS;
    }

    /**
     * Load data (flat array) for Varien_Data_Form
     *
     * @return array
     */
    public function getFormData()
    {
        $data = $this->getData();
        $data = Mage::helper('Mage_XmlConnect_Helper_Data')->getDeviceHelper()->checkImages($data);
        return $this->_flatArray($data);
    }

    /**
     * Load data (flat array) for Varien_Data_Form
     *
     * @param array $subtree
     * @param string $prefix
     * @return array
     */
    protected function _flatArray($subtree, $prefix=null)
    {
        $result = array();
        foreach ($subtree as $key => $value) {
            if (is_null($prefix)) {
                $name = $key;
            } else {
                $name = $prefix . '[' . $key . ']';
            }

            if (is_array($value)) {
                $result = array_merge($result, $this->_flatArray($value, $name));
            } else {
                $result[$name] = $value;
            }
        }
        return $result;
    }

    /**
     * Like array_merge_recursive(), but string values will be replaced
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    protected function _configMerge(array $array1, array $array2)
    {
        $result = array();
        $keys = array_unique(array_merge(array_keys($array1), array_keys($array2)));
        foreach ($keys as $key) {
            if (!isset($array1[$key])) {
                $result[$key] = $array2[$key];
            } elseif (!isset($array2[$key])) {
                $result[$key] = $array1[$key];
            } elseif (is_scalar($array1[$key]) || is_scalar($array2[$key])) {
                $result[$key] = $array2[$key];
            } else {
                $result[$key] = $this->_configMerge($array1[$key], $array2[$key]);
            }
        }
        return $result;
    }

    /**
     * Set default configuration data
     *
     * @return null
     */
    public function loadDefaultConfiguration()
    {
        $this->setCode($this->getCodePrefix());
        $this->setConf(Mage::helper('Mage_XmlConnect_Helper_Data')->getDeviceHelper()->getDefaultConfiguration());
    }

    /**
     * Return first part for application code field
     *
     * @return string
     */
    public function getCodePrefix()
    {
        return substr(Mage::app()->getStore($this->getStoreId())->getCode(), 0, self::APP_PREFIX_CUT_LENGTH)
            . substr($this->getType(), 0, self::APP_PREFIX_CUT_LENGTH);
    }

    /**
     * Checks if application code field has autoincrement
     *
     * @return bool
     */
    public function isCodePrefixed()
    {
        $suffix = substr($this->getCode(), self::APP_PREFIX_CUT_LENGTH * 2);
        return !empty($suffix);
    }

    /**
     * Load application configuration
     *
     * @deprecated Serialized config storage has been removed
     * @return array
     */
    public function prepareConfiguration()
    {
        return $this->getData('conf');
    }

    /**
     * Get config formatted for rendering
     *
     * @return array
     */
    public function getRenderConf()
    {
        $result = Mage::helper('Mage_XmlConnect_Helper_Data')->getDeviceHelper()->getDefaultConfiguration();
        $result = $result['native'];

        if (isset($this->_data['conf'])) {
            if (isset($this->_data['conf']['native'])) {
                $result = $this->_configMerge($result, $this->_data['conf']['native']);
            }
            if (isset($this->_data['conf']['extra'])) {
                $extra = $this->_data['conf']['extra'];
                if (isset($extra['tabs'])) {
                    $tabs = Mage::getModel('Mage_XmlConnect_Model_Tabs', $extra['tabs']);
                    $result['tabBar']['tabs'] = $tabs;
                }
                if (isset($extra['fontColors'])) {
                    if (!empty($extra['fontColors']['header'])) {
                        $result['fonts']['Title1']['color'] = $extra['fontColors']['header'];
                    }
                    if (!empty($extra['fontColors']['primary'])) {
                        $result['fonts']['Title2']['color'] = $extra['fontColors']['primary'];
                        $result['fonts']['Title3']['color'] = $extra['fontColors']['primary'];
                        $result['fonts']['Text1']['color']  = $extra['fontColors']['primary'];
                        $result['fonts']['Text2']['color']  = $extra['fontColors']['primary'];
                        $result['fonts']['Title7']['color'] = $extra['fontColors']['primary'];
                    }
                    if (!empty($extra['fontColors']['secondary'])) {
                        $result['fonts']['Title4']['color'] = $extra['fontColors']['secondary'];
                        $result['fonts']['Title6']['color'] = $extra['fontColors']['secondary'];
                        $result['fonts']['Title8']['color'] = $extra['fontColors']['secondary'];
                        $result['fonts']['Title9']['color'] = $extra['fontColors']['secondary'];
                    }
                    if (!empty($extra['fontColors']['price'])) {
                        $result['fonts']['Title5']['color'] = $extra['fontColors']['price'];
                    }
                }
            }
        }

        /** @var $helperImage Mage_XmlConnect_Helper_Image */
        $helperImage = Mage::helper('Mage_XmlConnect_Helper_Image');
        $paths = $helperImage->getInterfaceImagesPathsConf();

        foreach ($paths as $confPath => $dataPath) {
            $imageNodeValue =& $helperImage->findPath($result, $dataPath);

            if (!$helperImage->checkAndGetImagePath($imageNodeValue)) {
                /**
                 * We set empty string to get default image if original was missing in some reason
                 */
                $imageNodeValue = '';
            } else {
                /**
                 * Creating file ending (some_inner/some_dir/filename.png) For url
                 */
                $imageNodeValue = $helperImage->getFileCustomDirSuffixAsUrl($confPath, $imageNodeValue);
            }
        }
        $result = $this->_absPath($result);

        /**
         * General configuration
         */
        $result['general']['updateTimeUTC'] = strtotime($this->getUpdatedAt());
        $result['general']['browsingMode'] = $this->getBrowsingMode();
        $result['general']['currencyCode'] = Mage::app()->getStore($this->getStoreId())->getDefaultCurrencyCode();
        $result['general']['secureBaseUrl'] = $this->getSecureBaseUrl();

        $maxRecipients  = 0;
        $allowGuest     = 0;
        if (Mage::getStoreConfig(Mage_Sendfriend_Helper_Data::XML_PATH_ENABLED)) {
            $maxRecipients = Mage::getStoreConfig(Mage_Sendfriend_Helper_Data::XML_PATH_MAX_RECIPIENTS);
            $allowGuest = Mage::getStoreConfig(Mage_Sendfriend_Helper_Data::XML_PATH_ALLOW_FOR_GUEST);
        }
        $result['general']['emailToFriendMaxRecepients'] = $maxRecipients;
        $result['general']['emailAllowGuest'] = $allowGuest;
        $result['general']['primaryStoreLang'] = Mage::app()
            ->getStore($this->getStoreId())->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE);
        $result['general']['magentoVersion'] = Mage::getVersion();
        $result['general']['copyright'] = Mage::getStoreConfig(
            self::XML_PATH_DESIGN_FOOTER_COPYRIGHT, $this->getStoreId()
        );
        $result['general']['xmlconnectVersion'] = Mage::getConfig()->getNode(self::XML_PATH_MODULE_VERSION);

        $result['general']['isAllowedGuestCheckout'] = Mage::helper('Mage_Checkout_Helper_Data')
                ->isAllowedGuestCheckout(Mage::getSingleton('Mage_Checkout_Model_Session')->getQuote());

        /**
         * Check is guest can post product reviews
         */
        if (Mage::helper('Mage_Review_Helper_Data')->getIsGuestAllowToWrite()) {
            $result['general']['isAllowedGuestReview'] = '1';
        } else {
            $result['general']['isAllowedGuestReview'] = '0';
        }

        /**
        * Check is wishlist enabled in a config
        */
        if (Mage::getStoreConfigFlag('wishlist/general/active')) {
            $result['general']['wishlistEnable'] = '1';
        } else {
            $result['general']['wishlistEnable'] = '0';
        }

        /**
         * "Use Secure URLs in Frontend" flag
         */
        $result['general']['useSecureURLInFrontend'] = $this->getUseSecureURLInFrontend();

        /**
         * Is enabled Store credit functionality
         */
        if (is_object(Mage::getConfig()->getNode('modules/Enterprise_CustomerBalance'))) {
            $storeCreditFlag = Mage::getStoreConfig(Enterprise_CustomerBalance_Helper_Data::XML_PATH_ENABLED);
            $isStoreCreditEnable = (int)$storeCreditFlag;
            $canShowHistoryFlag = (int) Mage::getStoreConfigFlag(
                'customer/enterprise_customerbalance/show_history'
            );
        } else {
            $isStoreCreditEnable = $canShowHistoryFlag = 0;
        }
        $result['general']['isStoreCreditEnabled'] = $isStoreCreditEnable;
        $result['general']['isStoreCreditHistoryEnabled'] = $canShowHistoryFlag;

        /**
         * Is available Gift Card functionality
         */
        $result['general']['isGiftcardEnabled'] = (int) is_object(
            Mage::getConfig()->getNode('modules/Enterprise_GiftCard')
        );

        /**
         * PayPal configuration
         */
        $result['paypal']['businessAccount'] = Mage::getModel('Mage_Paypal_Model_Config')->businessAccount;
        $result['paypal']['merchantLabel'] = $this->getData('conf/special/merchantLabel');

        $isActive = 0;
        $paypalMepIsAvailable = Mage::getModel('Mage_XmlConnect_Model_Payment_Method_Paypal_Mep')->isAvailable(null);
        if ($paypalMepIsAvailable && isset($result['paypal']['isActive'])) {
            $isActive = (int) $result['paypal']['isActive'];
        }
        $result['paypal']['isActive'] = $isActive;

        $paypalMeclIsAvailable = Mage::getModel('Mage_XmlConnect_Model_Payment_Method_Paypal_Mecl')->isAvailable(null);

        /**
         * PayPal Mobile Express Library Checkout
         */
        $result['paypalMecl']['isActive'] = (int) (
            $paypalMeclIsAvailable
            && $this->getData('config_data/payment/paypalmecl_is_active')
        );

        if ((int)Mage::getStoreConfig(self::XML_PATH_GENERAL_RESTRICTION_IS_ACTIVE)) {
            $result['website_restrictions']['mode'] = (int)Mage::getStoreConfig(
                self::XML_PATH_GENERAL_RESTRICTION_MODE
            );
        }

        ksort($result);
        return $result;
    }

    /**
     * Get secure base url
     *
     * @return string
     */
    public function getSecureBaseUrl()
    {
        return Mage::getStoreConfig(self::XML_PATH_SECURE_BASE_LINK_URL, $this->getStoreId());
    }

    /**
     * Is forced front secure url
     *
     * @return int
     */
    public function getUseSecureURLInFrontend()
    {
        return (int) Mage::getStoreConfigFlag(Mage_Core_Model_Store::XML_PATH_SECURE_IN_FRONTEND);
    }

    /**
     * Return current screen_size parameter
     *
     * @return string
     */
    public function getScreenSize()
    {
        if (!isset($this->_data['screen_size'])) {
            $this->_data['screen_size'] = self::APP_SCREEN_SIZE_DEFAULT;
        }
        return $this->_data['screen_size'];
    }

    /**
     * Setter
     * for current screen_size parameter
     *
     * @param string $screenSize
     * @return this
     */
    public function setScreenSize($screenSize)
    {
        $this->_data['screen_size'] = Mage::helper('Mage_XmlConnect_Helper_Image')->filterScreenSize((string) $screenSize);
        return $this;
    }

    /**
     * Return Enabled Tabs array from actual config
     *
     * @return array:
     */
    public function getEnabledTabsArray()
    {
        if ($this->getData('conf/extra/tabs')) {
            return Mage::getModel('Mage_XmlConnect_Model_Tabs', $this->getData('conf/extra/tabs'))->getRenderTabs();
        }
        return array();
    }

    /**
     * Change URLs to absolute
     *
     * @param array $subtree
     * @return array
     */
    protected function _absPath($subtree)
    {
        foreach ($subtree as $key => $value) {
            if (!empty($value)) {
                if (is_array($value)) {
                    $subtree[$key] = $this->_absPath($value);
                } elseif (strtolower(substr($key, -4)) == 'icon' || strtolower(substr($key, -5)) == 'image') {
                    $subtree[$key] = Mage::getBaseUrl('media') . 'xmlconnect/' . $value;
                }
            }
        }
        return $subtree;
    }

    /**
     * Return content pages
     *
     * @return array
     */
    public function getPages()
    {
        if (isset($this->_data['conf']['native']['pages'])) {
            return $this->_data['conf']['native']['pages'];
        }
        return array();
    }

    /**
     * Get configuration model
     *
     * @return Mage_XmlConnect_Model_ConfigData
     */
    public function getConfigModel()
    {
        return $this->_configModel;
    }

    /**
     * Processing object before save data
     *
     * @return Mage_XmlConnect_Model_Application
     */
    protected function _beforeSave()
    {
        $this->setUpdatedAt(Mage::getSingleton('Mage_Core_Model_Date')->gmtDate());
        return $this;
    }

    /**
     * Processing object after save data
     *
     * @return Mage_XmlConnect_Model_Application
     */
    protected function _afterSave()
    {
        $this->_saveConfigData();
        $this->_saveDeprecatedConfig();
        parent::_afterSave();
        return $this;
    }

    /**
     * Save configuration data of application model
     *
     * @return Mage_XmlConnect_Model_Application
     */
    protected function _saveConfigData()
    {
        $configuration = $this->getData('config_data');
        if (is_array($configuration)) {
            $this->getConfigModel()->setConfigData($this->getId(), $configuration)->initSaveConfig();
        }
        return $this;
    }

    /**
     * Save old deprecated config to application config data table
     *
     * @deprecated Serialized config storage has been removed
     * @return Mage_XmlConnect_Model_Application
     */
    private function _saveDeprecatedConfig()
    {
        $deprecatedConfig = $this->getData('conf');
        if (is_array($deprecatedConfig)) {
            $this->getConfigModel()->saveConfig(
                $this->getId(), $this->convertOldConfing($deprecatedConfig), self::DEPRECATED_CONFIG_FLAG
            );
        }
        return $this;
    }

    /**
     * Convert deprecated configuration array to new standard
     *
     * @deprecated Serialized config storage has been removed
     * @param array $conf
     * @param bool $path
     * @return array
     */
    public function convertOldConfing(array $conf, $path = false)
    {
        $result = array();
        foreach ($conf as $key => $val) {
            $key = $path ? $path . '/' . $key : $key;
            if (is_array($val)) {
                $result += $this->convertOldConfing($val, $key);
            } else {
                $result[$key] = $val;
            }
        }
        return $result;
    }

    /**
     * Load configuration data (from serialized blob)
     *
     * @return Mage_XmlConnect_Model_Application
     */
    public function loadConfiguration()
    {
        if (!$this->_isConfigurationLoaded) {
            if ($this->getId()) {
                $this->_loadDeprecatedConfig()->_loadConfigData();
                $this->_isConfigurationLoaded = true;
            }
        }
        return $this;
    }

    /**
     * Load configuration data
     *
     * @internal re-factoring in progress
     * @return Mage_XmlConnect_Model_Application
     */
    protected function _loadConfigData()
    {
        $configuration = $this->getConfigModel()->getCollection()->addArrayFilter(array(
            'application_id' => $this->getId(),
            'category' => 'payment'
        ))->toOptionArray();

        $this->setData('config_data', $configuration);
        return $this;
    }

    /**
     * Load deprecated configuration
     *
     * @deprecated Serialized config storage has been removed
     * @return Mage_XmlConnect_Model_Application
     */
    private function _loadDeprecatedConfig()
    {
        $configuration = $this->_convertConfig(
            $this->getConfigModel()->getCollection()->addArrayFilter(array(
                'application_id' => $this->getId(),
                'category' => self::DEPRECATED_CONFIG_FLAG
            ))->toOptionArray()
        );
        $this->setData('conf', $configuration);
        return $this;
    }

    /**
     * Convert old config data array
     *
     * @deprecated  Serialized config storage has been removed
     * @param  $config
     * @return array
     */
    protected function _convertConfig($config)
    {
        $result = array();
        foreach ($config as $values) {
            foreach ($values as $path => $value) {
                if (preg_match('@[^\w\/]@', $path)) {
                    Mage::throwException(
                        Mage::helper('Mage_XmlConnect_Helper_Data')->__('Unsupported character in path: "%s"', $path)
                    );
                }
                $keyArray = explode('/', $path);
                $keys = '$result["' . implode('"]["', $keyArray) . '"]';
                eval($keys . ' = $value;');
            }
        }
        return $result;
    }

    /**
     * Load application by code
     *
     * @param string $code
     * @return Mage_XmlConnect_Model_Application
     */
    public function loadByCode($code)
    {
        $this->_getResource()->load($this, $code, 'code');
        return $this;
    }

    /**
     * Loads submit tab data from xmlconnect/history table
     *
     * @return bool
     */
    public function loadSubmit()
    {
        $isResubmitAction = false;
        if ($this->getId()) {
            $params = $this->getLastParams();
            if (!empty($params)) {
                // Using Pointer !
                $conf = &$this->_data['conf'];
                if (!isset($conf['submit_text']) || !is_array($conf['submit_text'])) {
                    $conf['submit_text'] = array();
                }
                if (!isset($conf['submit_restore']) || !is_array($conf['submit_restore'])) {
                    $conf['submit_restore'] = array();
                }
                foreach ($params as $id => $value) {
                    $deviceImages = Mage::helper('Mage_XmlConnect_Helper_Data')
                        ->getDeviceHelper()
                        ->getSubmitImages();

                    if (!in_array($id, $deviceImages)) {
                        $conf['submit_text'][$id] = $value;
                    } else {
                        $conf['submit_restore'][$id] = $value;
                    }
                    $isResubmitAction = true;
                }
            }
        }
        $this->setIsResubmitAction($isResubmitAction);
        return $isResubmitAction;
    }

    /**
     * Returns ( image[ ID ] => "SRC" )  array
     *
     * @return array
     */
    public function getImages()
    {
        $images = array();
        $params = $this->getLastParams();
        $deviceImages = Mage::helper('Mage_XmlConnect_Helper_Data')
            ->getDeviceHelper()
            ->getSubmitImages();

        foreach ($deviceImages as $id) {
            $path = $this->getData('conf/submit/'.$id);
            $basename = null;
            if (!empty($path)) {
                /**
                 * Fetching data from session restored array
                 */
                 $basename = basename($path);
            } elseif (isset($params[$id])) {
               /**
                * Fetching data from submission history table record
                *
                * converting :  "@\var\somedir\media\xmlconnect\form_icon_6.png"
                * to "\var\somedir\media\xmlconnect\forn_icon_6.png"
                */
                $basename = basename($params[$id]);
            }
            if (!empty($basename)) {
                $images['conf/submit/'.$id] = Mage::getBaseUrl('media') . 'xmlconnect/'
                    . Mage::helper('Mage_XmlConnect_Helper_Image')->getFileDefaultSizeSuffixAsUrl($basename);
            }
        }
        return $images;
    }

    /**
     * Return last submitted data from history table
     *
     * @return array
     */
    public function getLastParams()
    {
        if (!isset($this->_lastParams)) {
            $this->_lastParams = Mage::getModel('Mage_XmlConnect_Model_History')->getLastParams($this->getId());
        }
        return $this->_lastParams;
    }

    /**
     * Validate application data
     *
     * @return array|bool
     */
    public function validate()
    {
        $errors = array();

        $validateConf = $this->_validateConf();
        if ($validateConf !== true) {
            $errors = $validateConf;
        }

        if (!Zend_Validate::is($this->getName(), 'NotEmpty')) {
            $errors[] = Mage::helper('Mage_XmlConnect_Helper_Data')->__('Please enter "App Title".');
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }

    /**
     * Validate submit application data
     *
     * @param array $params
     * @return array|bool
     */
    public function validateSubmit($params)
    {
        $errors = array();
        $validateConf = $this->_validateConf();
        if ($validateConf !== true) {
            $errors = $validateConf;
        }

        $submitErrors = Mage::helper('Mage_XmlConnect_Helper_Data')->getDeviceHelper($this)->validateSubmit($params);

        if (count($submitErrors)) {
            $errors = array_merge($errors, $submitErrors);
        }
        if (empty($errors)) {
            return true;
        }
        return $errors;
    }

    /**
     * Check config for valid values
     *
     * @return bool|array
     */
    protected function _validateConf()
    {
        $conf   = $this->getConf();
        $native = isset($conf['native']) && is_array($conf['native']) ? $conf['native'] : false;
        $errors = Mage::helper('Mage_XmlConnect_Helper_Data')->getDeviceHelper($this)->validateConfig($native);

        foreach ($this->_socialNetValidationArray as $networkKey) {
            if (isset($native['socialNetworking'][$networkKey]['isActive'])
                && $native['socialNetworking'][$networkKey]['isActive']
            ) {
                if ($networkKey !== Mage_XmlConnect_Helper_Data::SOCIAL_NETWORK_FACEBOOK) {
                    $networkName = ucfirst($networkKey);
                    if (!isset($native['socialNetworking'][$networkKey]['apiKey'])
                        || !Zend_Validate::is($native['socialNetworking'][$networkKey]['apiKey'], 'NotEmpty')
                    ) {
                        $errors[] = Mage::helper('Mage_XmlConnect_Helper_Data')->__('%s API Key required.', $networkName);
                    }
                    if (!isset($native['socialNetworking'][$networkKey]['secretKey'])
                        || !Zend_Validate::is($native['socialNetworking'][$networkKey]['secretKey'], 'NotEmpty')
                    ) {
                        $errors[] = Mage::helper('Mage_XmlConnect_Helper_Data')->__('%s Secret Key required.', $networkName);
                    }
                } else {
                    $networkName = ucfirst($networkKey);
                    if (!isset($native['socialNetworking'][$networkKey]['appID'])
                        || !Zend_Validate::is($native['socialNetworking'][$networkKey]['appID'], 'NotEmpty')
                    ) {
                        $errors[] = Mage::helper('Mage_XmlConnect_Helper_Data')->__('%s Application ID required.', $networkName);
                    }
                }
            }
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }

    /**
     * Imports post/get data into the model
     *
     * @param array $data - $_REQUEST[]
     * @return array
     */
    public function prepareSubmitParams($data)
    {
        $params = array();
        if (isset($data['conf']) && is_array($data['conf'])) {

            if (isset($data['conf']['submit_text']) && is_array($data['conf']['submit_text'])) {
                $params = $data['conf']['submit_text'];
            }

            $params['name'] = $this->getName();
            $params['code'] = $this->getCode();
            $params['type'] = $this->getType();
            $params['url'] = Mage::getUrl('xmlconnect/configuration/index', array(
                '_store' => $this->getStoreId(), '_nosid' => true, 'app_code' => $this->getCode()
            ));

            $params['magentoversion'] = Mage::getVersion();

            if (isset($params['country']) && is_array($params['country'])) {
                $params['country'] = implode(',', $params['country']);
            }
            if ($this->getIsResubmitAction()) {
                if (isset($params['resubmission_activation_key'])) {
                    $params['resubmission_activation_key'] = trim($params['resubmission_activation_key']);
                    $params['key'] = $params['resubmission_activation_key'];
                } else {
                    $params['key'] = '';
                }
            } else {
                $params['key'] = isset($params['key']) ? trim($params['key']) : '';
            }

            // processing files
            $submit = array();
            if (isset($this->_data['conf']['submit']) && is_array($this->_data['conf']['submit'])) {
                 $submit = $this->_data['conf']['submit'];
            }

            $submitRestore  = array();
            if (isset($this->_data['conf']['submit_restore']) && is_array($this->_data['conf']['submit_restore'])) {
                $submitRestore = $this->_data['conf']['submit_restore'];
            }

            $deviceImages = Mage::helper('Mage_XmlConnect_Helper_Data')->getDeviceHelper()->getSubmitImages();

            foreach ($deviceImages as $id) {
                if (isset($submit[$id])) {
                    $params[$id] = '@' . $submit[$id];
                } elseif (isset($submitRestore[$id])) {
                    $params[$id] = $submitRestore[$id];
                }
            }
        }
        $this->setSubmitParams($params);
        return $params;
    }

    /**
     * Retrieve Store Id
     *
     * @return int
     */
    public function getStoreId()
    {
        if ($this->hasData('store_id')) {
            return $this->getData('store_id');
        }
        return Mage::app()->getStore()->getId();
    }

    /**
     * Getter, returns activation key for current application
     *
     * @return string|null
     */
    public function getActivationKey()
    {
        $key = null;
        if (isset($this->_data['conf']['submit_text']['key'])) {
            $key = $this->_data['conf']['submit_text']['key'];
        }
        return $key;
    }

    /**
     * Perform update for all applications "updated at" parameter with current date
     *
     * @return Mage_XmlConnect_Model_Application
     */
    public function updateAllAppsUpdatedAtParameter()
    {
        $this->_getResource()->updateAllAppsUpdatedAtParameter();
        return $this;
    }

    /**
     * Checks if notifications is active
     *
     * @return boolean
     */
    public function isNotificationsActive()
    {
        return (boolean)$this->loadConfiguration()->getData('conf/native/notifications/isActive');
    }

    /**
     * Getter return concatenated user and password
     *
     * @return string
     */
    public function getUserpwd()
    {
        return $this->loadConfiguration()->getAppKey() . ':' . $this->getAppMasterSecret();
    }

    /**
     * Getter for Application Key
     *
     * @return string
     */
    public function getAppKey()
    {
        return $this->getData('conf/native/notifications/applicationKey');
    }

    /**
     * Getter for Application Secret
     *
     * @return string
     */
    public function getAppSecret()
    {
        return $this->getData('conf/native/notifications/applicationSecret');
    }

    /**
     * Getter for Application Master Secret
     *
     * @return string
     */
    public function getAppMasterSecret()
    {
        return $this->getData('conf/native/notifications/applicationMasterSecret');
    }

    /**
     * Getter for Application Cache Lifetime
     *
     * @return int|string
     */
    public function getCacheLifetime()
    {
        $lifetime = (int)$this->loadConfiguration()->getData('conf/native/cacheLifetime');
        return $lifetime <= 0 ? '' : $lifetime;
    }

    /**
     * Get delete on update paths for config data
     *
     * @return array
     */
    public function getDeleteOnUpdateConfig()
    {
        return $this->_deleteOnUpdateConfig;
    }

    /**
     * Set delete on update paths for config data
     *
     * @param array $pathsToDelete
     * @return Mage_XmlConnect_Model_Application
     */
    public function setDeleteOnUpdateConfig(array $pathsToDelete)
    {
        $this->_deleteOnUpdateConfig = array_merge($this->_deleteOnUpdateConfig, $pathsToDelete);
        return $this;
    }
}
