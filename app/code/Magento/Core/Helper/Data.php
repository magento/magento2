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
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Helper;

/**
 * Core data helper
 */
class Data extends \Magento\Core\Helper\AbstractHelper
{
    const XML_PATH_DEFAULT_COUNTRY              = 'general/country/default';
    const XML_PATH_DEV_ALLOW_IPS                = 'dev/restrict/allow_ips';
    const XML_PATH_CONNECTION_TYPE              = 'global/resources/default_setup/connection/type';

    const XML_PATH_SINGLE_STORE_MODE_ENABLED = 'general/single_store_mode/enabled';

    /**
     * Const for correct dividing decimal values
     */
    const DIVIDE_EPSILON = 10000;

    /**
     * Config path to mail sending setting that shows if email communications are disabled
     */
    const XML_PATH_SYSTEM_SMTP_DISABLE = 'system/smtp/disable';

    protected $_allowedFormats = array(
        \Magento\Core\Model\LocaleInterface::FORMAT_TYPE_FULL,
        \Magento\Core\Model\LocaleInterface::FORMAT_TYPE_LONG,
        \Magento\Core\Model\LocaleInterface::FORMAT_TYPE_MEDIUM,
        \Magento\Core\Model\LocaleInterface::FORMAT_TYPE_SHORT
    );

    /**
     * Core event manager proxy
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\Core\Model\Cache\Config
     */
    protected $_cacheConfig;

    /**
     * @var \Magento\Core\Model\Fieldset\Config
     */
    protected $_fieldsetConfig;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @var \Magento\Core\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * @var \Magento\Core\Model\Locale
     */
    protected $_locale;

    /**
     * @var \Magento\Core\Model\Date
     */
    protected $_dateModel;

    /**
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * @var boolean
     */
    protected $_dbCompatibleMode;

    /**
     * @var \Magento\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $_remoteAddress;

    /**
     * @param Context $context
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\Core\Model\Locale $locale
     * @param \Magento\Core\Model\Date $dateModel
     * @param \Magento\App\State $appState
     * @param bool $dbCompatibleMode
     */
    public function __construct(
        \Magento\Core\Helper\Context $context,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\Core\Model\Locale $locale,
        \Magento\Core\Model\Date $dateModel,
        \Magento\App\State $appState,
        $dbCompatibleMode = true
    ) {
        $this->_eventManager = $eventManager;
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_remoteAddress = $context->getRemoteAddress();
        parent::__construct($context);
        $this->_cacheConfig = $context->getCacheConfig();
        $this->_fieldsetConfig = $context->getFieldsetConfig();
        $this->_storeManager = $storeManager;
        $this->_locale = $locale;
        $this->_dateModel = $dateModel;
        $this->_appState = $appState;
        $this->_dbCompatibleMode = $dbCompatibleMode;
    }

    /**
     * Convert and format price value for current application store
     *
     * @param   float $value
     * @param   bool $format
     * @param   bool $includeContainer
     * @return  mixed
     */
    public function currency($value, $format = true, $includeContainer = true)
    {
        return $this->currencyByStore($value, null, $format, $includeContainer);
    }

    /**
     * Convert and format price value for specified store
     *
     * @param   float $value
     * @param   int|\Magento\Core\Model\Store $store
     * @param   bool $format
     * @param   bool $includeContainer
     * @return  mixed
     */
    public function currencyByStore($value, $store = null, $format = true, $includeContainer = true)
    {
        try {
            if (!($store instanceof \Magento\Core\Model\Store)) {
                $store = $this->_app->getStore($store);
            }

            $value = $store->convertPrice($value, $format, $includeContainer);
        }
        catch (\Exception $e){
            $value = $e->getMessage();
        }

        return $value;
    }

    /**
     * Format and convert currency using current store option
     *
     * @param   float $value
     * @param   bool $includeContainer
     * @return  string
     */
    public function formatCurrency($value, $includeContainer = true)
    {
        return $this->currency($value, true, $includeContainer);
    }

    /**
     * Formats price
     *
     * @param float $price
     * @param bool $includeContainer
     * @return string
     */
    public function formatPrice($price, $includeContainer = true)
    {
        return $this->_storeManager->getStore()->formatPrice($price, $includeContainer);
    }

    /**
     * Format date using current locale options and time zone.
     *
     * @param   date|Zend_Date|null $date
     * @param   string              $format   See \Magento\Core\Model\LocaleInterface::FORMAT_TYPE_* constants
     * @param   bool                $showTime Whether to include time
     * @return  string
     */
    public function formatDate(
        $date = null,
        $format = \Magento\Core\Model\LocaleInterface::FORMAT_TYPE_SHORT,
        $showTime = false
    ) {
        if (!in_array($format, $this->_allowedFormats, true)) {
            return $date;
        }
        if (!($date instanceof \Zend_Date) && $date && !strtotime($date)) {
            return '';
        }
        if (is_null($date)) {
            $date = $this->_locale->date(
                $this->_dateModel->gmtTimestamp(),
                null,
                null
            );
        } elseif (!$date instanceof \Zend_Date) {
            $date = $this->_locale->date(strtotime($date), null, null);
        }

        if ($showTime) {
            $format = $this->_locale->getDateTimeFormat($format);
        } else {
            $format = $this->_locale->getDateFormat($format);
        }

        return $date->toString($format);
    }

    /**
     * Format time using current locale options
     *
     * @param   date|Zend_Date|null $time
     * @param   string              $format
     * @param   bool                $showDate
     * @return  string
     */
    public function formatTime(
        $time = null,
        $format = \Magento\Core\Model\LocaleInterface::FORMAT_TYPE_SHORT,
        $showDate = false
    ) {
        if (!in_array($format, $this->_allowedFormats, true)) {
            return $time;
        }

        if (is_null($time)) {
            $date = $this->_locale->date(time());
        } else if ($time instanceof \Zend_Date) {
            $date = $time;
        } else {
            $date = $this->_locale->date(strtotime($time));
        }

        if ($showDate) {
            $format = $this->_locale->getDateTimeFormat($format);
        } else {
            $format = $this->_locale->getTimeFormat($format);
        }

        return $date->toString($format);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isDevAllowed($storeId = null)
    {
        $allow = true;

        $allowedIps = $this->_coreStoreConfig->getConfig(self::XML_PATH_DEV_ALLOW_IPS, $storeId);
        $remoteAddr = $this->_remoteAddress->getRemoteAddress();
        if (!empty($allowedIps) && !empty($remoteAddr)) {
            $allowedIps = preg_split('#\s*,\s*#', $allowedIps, null, PREG_SPLIT_NO_EMPTY);
            if (array_search($remoteAddr, $allowedIps) === false
                && array_search($this->_httpHeader->getHttpHost(), $allowedIps) === false) {
                $allow = false;
            }
        }

        return $allow;
    }

    /**
     * Get information about available cache types
     *
     * @return array
     */
    public function getCacheTypes()
    {
        $types = array();
        foreach ($this->_cacheConfig->getTypes() as $type => $node) {
            $types[$type] = $node['label'];
        }
        return $types;
    }

    /**
     * Copy data from object|array to object|array containing fields
     * from fieldset matching an aspect.
     *
     * Contents of $aspect are a field name in target object or array.
     * If targetField attribute is not provided - will be used the same name as in the source object or array.
     *
     * @param string $fieldset
     * @param string $aspect
     * @param array|\Magento\Object $source
     * @param array|\Magento\Object $target
     * @param string $root
     * @return array|\Magento\Object|null the value of $target
     */
    public function copyFieldsetToTarget($fieldset, $aspect, $source, $target, $root='global')
    {
        if (!$this->_isFieldsetInputValid($source, $target)) {
            return null;
        }
        $fields = $this->_fieldsetConfig->getFieldset($fieldset, $root);
        if (is_null($fields)) {
            return $target;
        }
        $targetIsArray = is_array($target);

        foreach ($fields as $code => $node) {
            if (empty($node[$aspect])) {
                continue;
            }

            $value = $this->_getFieldsetFieldValue($source, $code);

            $targetCode = (string)$node[$aspect];
            $targetCode = $targetCode == '*' ? $code : $targetCode;

            if ($targetIsArray) {
                $target[$targetCode] = $value;
            } else {
                $target->setDataUsingMethod($targetCode, $value);
            }
        }

        $eventName = sprintf('core_copy_fieldset_%s_%s', $fieldset, $aspect);
        $this->_eventManager->dispatch($eventName, array(
            'target' => $target,
            'source' => $source,
            'root'   => $root
        ));

        return $target;
    }

    /**
     * Check if source and target are valid input for converting using fieldset
     *
     * @param array|\Magento\Object $source
     * @param array|\Magento\Object $target
     * @return bool
     */
    private function _isFieldsetInputValid($source, $target)
    {
        return (is_array($source) || $source instanceof \Magento\Object)
        && (is_array($target) || $target instanceof \Magento\Object);
    }

    /**
     * Get value of source by code
     *
     * @param $source
     * @param $code
     * @return null
     */
    private function _getFieldsetFieldValue($source, $code)
    {
        if (is_array($source)) {
            $value = isset($source[$code]) ? $source[$code] : null;
        } else {
            $value = $source->getDataUsingMethod($code);
        }
        return $value;
    }

    /**
     * Decorate a plain array of arrays or objects
     * The array actually can be an object with Iterator interface
     *
     * Keys with prefix_* will be set:
     * *_is_first - if the element is first
     * *_is_odd / *_is_even - for odd/even elements
     * *_is_last - if the element is last
     *
     * The respective key/attribute will be set to element, depending on object it is or array.
     * \Magento\Object is supported.
     *
     * $forceSetAll true will cause to set all possible values for all elements.
     * When false (default), only non-empty values will be set.
     *
     * @param mixed $array
     * @param string $prefix
     * @param bool $forceSetAll
     * @return mixed
     */
    public function decorateArray($array, $prefix = 'decorated_', $forceSetAll = false)
    {
        // check if array or an object to be iterated given
        if (!(is_array($array) || is_object($array))) {
            return $array;
        }

        $keyIsFirst = "{$prefix}is_first";
        $keyIsOdd   = "{$prefix}is_odd";
        $keyIsEven  = "{$prefix}is_even";
        $keyIsLast  = "{$prefix}is_last";

        $count  = count($array); // this will force Iterator to load
        $i      = 0;
        $isEven = false;
        foreach ($array as $key => $element) {
            if (is_object($element)) {
                $this->_decorateArrayObject($element, $keyIsFirst, (0 === $i), $forceSetAll || (0 === $i));
                $this->_decorateArrayObject($element, $keyIsOdd, !$isEven, $forceSetAll || !$isEven);
                $this->_decorateArrayObject($element, $keyIsEven, $isEven, $forceSetAll || $isEven);
                $isEven = !$isEven;
                $i++;
                $this->_decorateArrayObject($element, $keyIsLast, ($i === $count), $forceSetAll || ($i === $count));
            } elseif (is_array($element)) {
                if ($forceSetAll || (0 === $i)) {
                    $array[$key][$keyIsFirst] = (0 === $i);
                }
                if ($forceSetAll || !$isEven) {
                    $array[$key][$keyIsOdd] = !$isEven;
                }
                if ($forceSetAll || $isEven) {
                    $array[$key][$keyIsEven] = $isEven;
                }
                $isEven = !$isEven;
                $i++;
                if ($forceSetAll || ($i === $count)) {
                    $array[$key][$keyIsLast] = ($i === $count);
                }
            }
        }

        return $array;
    }

    /**
     * Mark passed object with specified flag and appropriate value.
     *
     * @param \Magento\Object $element
     * @param string $key
     * @param mixed $value
     * @param bool $dontSkip
     */
    private function _decorateArrayObject($element, $key, $value, $dontSkip)
    {
        if ($dontSkip && $element instanceof \Magento\Object) {
            $element->setData($key, $value);
        }
    }

    /**
     * Encode the mixed $valueToEncode into the JSON format
     *
     * @param mixed $valueToEncode
     * @param  boolean $cycleCheck Optional; whether or not to check for object recursion; off by default
     * @param  array $options Additional options used during encoding
     * @return string
     */
    public function jsonEncode($valueToEncode, $cycleCheck = false, $options = array())
    {
        $json = \Zend_Json::encode($valueToEncode, $cycleCheck, $options);
        if ($this->_translator->isAllowed()) {
            $this->_translator->processResponseBody($json, true);
        }

        return $json;
    }

    /**
     * Decodes the given $encodedValue string which is
     * encoded in the JSON format
     *
     * @param string $encodedValue
     * @param int $objectDecodeType
     * @return mixed
     */
    public function jsonDecode($encodedValue, $objectDecodeType = \Zend_Json::TYPE_ARRAY)
    {
        return \Zend_Json::decode($encodedValue, $objectDecodeType);
    }

    /**
     * Return default country code
     *
     * @param \Magento\Core\Model\Store|string|int $store
     * @return string
     */
    public function getDefaultCountry($store = null)
    {
        return $this->_coreStoreConfig->getConfig(self::XML_PATH_DEFAULT_COUNTRY, $store);
    }

    /**
     * Check whether database compatible mode is used (configs enable it for MySQL by default).
     *
     * @return bool
     */
    public function useDbCompatibleMode()
    {
        return $this->_dbCompatibleMode;
    }

    /**
     * Check if Single-Store mode is enabled in configuration
     *
     * This flag only shows that admin does not want to show certain UI components at backend (like store switchers etc)
     * if Magento has only one store view but it does not check the store view collection
     *
     * @return bool
     */
    public function isSingleStoreModeEnabled()
    {
        return (bool) $this->_coreStoreConfig->getConfig(self::XML_PATH_SINGLE_STORE_MODE_ENABLED);
    }

    /**
     * Returns the translate model for this instance.
     *
     * @return \Magento\Core\Model\Translate
     */
    public function getTranslator()
    {
        return $this->_translator;
    }
}
