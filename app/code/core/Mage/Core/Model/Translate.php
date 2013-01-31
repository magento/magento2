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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Translate model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_Translate
{
    const CSV_SEPARATOR     = ',';
    const SCOPE_SEPARATOR   = '::';
    const CACHE_TAG         = 'translate';

    const CONFIG_KEY_AREA   = 'area';
    const CONFIG_KEY_LOCALE = 'locale';
    const CONFIG_KEY_STORE  = 'store';
    const CONFIG_KEY_DESIGN_THEME   = 'theme';

    const XML_PATH_LOCALE_INHERITANCE = 'global/locale/inheritance';

    /**
     * Default translation string
     */
    const DEFAULT_STRING = 'Translate String';

    /**
     * Locale name
     *
     * @var string
     */
    protected $_locale;

    /**
     * Translation object
     *
     * @var Zend_Translate_Adapter
     */
    protected $_translate;

    /**
     * Translator configuration array
     *
     * @var array
     */
    protected $_config;

    protected $_useCache = true;

    /**
     * Cache identifier
     *
     * @var string
     */
    protected $_cacheId;

    /**
     * Translation data
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Translation data for data scope (per module)
     *
     * @var array
     */
    protected $_dataScope;

    /**
     * Configuration flag to enable inline translations
     *
     * @var boolean
     */
    protected $_translateInline;

    /**
     * Configuration flag to local enable inline translations
     *
     * @var boolean
     */
    protected $_canUseInline = true;

    /**
     * Locale hierarchy (empty by default)
     *
     * @var array
     */
    protected $_localeHierarchy = array();

    /**
     * Initialize translate model
     *
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        if (isset($data['locale_hierarchy']) && is_array($data['locale_hierarchy'])) {
            $this->_localeHierarchy = $data['locale_hierarchy'];
        } else {
            // Try to load locale inheritance from Magento configuration
            $inheritanceNode = Mage::getConfig()->getNode(self::XML_PATH_LOCALE_INHERITANCE);
            if ($inheritanceNode instanceof Varien_Simplexml_Element) {
                $this->_localeHierarchy = Mage::helper('Mage_Core_Helper_Translate')->composeLocaleHierarchy(
                    $inheritanceNode->asCanonicalArray()
                );
            }
        }
    }

    /**
     * Initialization translation data
     *
     * @param string $area
     * @param bool $forceReload
     * @return Mage_Core_Model_Translate
     */
    public function init($area, $forceReload = false)
    {
        $this->setConfig(array(self::CONFIG_KEY_AREA=>$area));

        $this->_translateInline = Mage::getSingleton('Mage_Core_Model_Translate_Inline')
            ->isAllowed($area=='adminhtml' ? 'admin' : null);

        if (!$forceReload) {
            if ($this->_canUseCache()) {
                $this->_data = $this->_loadCache();
                if ($this->_data !== false) {
                    return $this;
                }
            }
            Mage::app()->removeCache($this->getCacheId());
        }

        $this->_data = array();

        foreach ($this->getModulesConfig() as $moduleName=>$info) {
            $info = $info->asArray();
            $this->_loadModuleTranslation($moduleName, $info['files'], $forceReload);
        }

        $this->_loadThemeTranslation($forceReload);
        $this->_loadDbTranslation($forceReload);

        if (!$forceReload && $this->_canUseCache()) {
            $this->_saveCache();
        }

        return $this;
    }

    /**
     * Retrieve modules configuration by translation
     *
     * @return Mage_Core_Model_Config_Element
     */
    public function getModulesConfig()
    {
        if (!Mage::getConfig()->getNode($this->getConfig(self::CONFIG_KEY_AREA).'/translate/modules')) {
            return array();
        }

        $config = Mage::getConfig()->getNode($this->getConfig(self::CONFIG_KEY_AREA).'/translate/modules')->children();
        if (!$config) {
            return array();
        }
        return $config;
    }

    /**
     * Initialize configuration
     *
     * @param   array $config
     * @return  Mage_Core_Model_Translate
     */
    public function setConfig($config)
    {
        $this->_config = $config;
        if (!isset($this->_config[self::CONFIG_KEY_LOCALE])) {
            $this->_config[self::CONFIG_KEY_LOCALE] = $this->getLocale();
        }
        if (!isset($this->_config[self::CONFIG_KEY_STORE])) {
            $this->_config[self::CONFIG_KEY_STORE] = Mage::app()->getStore()->getId();
        }
        if (!isset($this->_config[self::CONFIG_KEY_DESIGN_THEME])) {
            $this->_config[self::CONFIG_KEY_DESIGN_THEME] = Mage::getDesign()->getDesignTheme()->getId();
        }
        return $this;
    }

    /**
     * Retrieve config value by key
     *
     * @param   string $key
     * @return  mixed
     */
    public function getConfig($key)
    {
        if (isset($this->_config[$key])) {
            return $this->_config[$key];
        }
        return null;
    }

    /**
     * Load data from module translation files
     *
     * @param string $moduleName
     * @param array $files
     * @param boolean $forceReload
     * @return Mage_Core_Model_Translate
     */
    protected function _loadModuleTranslation($moduleName, $files, $forceReload = false)
    {
        $requiredLocaleList = $this->_composeRequiredLocaleList($this->getLocale());
        foreach ($files as $file) {
            foreach ($requiredLocaleList as $locale) {
                $moduleFilePath = $this->_getModuleFilePath($moduleName, $file, $locale);
                $this->_addData($this->_getFileData($moduleFilePath), $moduleName, $forceReload);
            }
        }
        return $this;
    }

    /**
     * Compose the list of locales which are required to translate text entity based on given locale
     *
     * @param string $locale
     * @return array
     */
    protected function _composeRequiredLocaleList($locale)
    {
        $requiredLocaleList = array($locale);
        if (isset($this->_localeHierarchy[$locale])) {
            $requiredLocaleList = array_merge($this->_localeHierarchy[$locale], $requiredLocaleList);
        }
        return $requiredLocaleList;
    }

    /**
     * Adding translation data
     *
     * @param array $data
     * @param string $scope
     * @return Mage_Core_Model_Translate
     */
    protected function _addData($data, $scope, $forceReload=false)
    {
        foreach ($data as $key => $value) {
            if ($key === $value) {
                continue;
            }
            $key    = $this->_prepareDataString($key);
            $value  = $this->_prepareDataString($value);
            if ($scope && isset($this->_dataScope[$key]) && !$forceReload ) {
                /**
                 * Checking previos value
                 */
                $scopeKey = $this->_dataScope[$key] . self::SCOPE_SEPARATOR . $key;
                if (!isset($this->_data[$scopeKey])) {
                    if (isset($this->_data[$key])) {
                        $this->_data[$scopeKey] = $this->_data[$key];
                        unset($this->_data[$key]);
                    }
                }
                $scopeKey = $scope . self::SCOPE_SEPARATOR . $key;
                $this->_data[$scopeKey] = $value;
            }
            else {
                $this->_data[$key]     = $value;
                $this->_dataScope[$key]= $scope;
            }
        }
        return $this;
    }

    protected function _prepareDataString($string)
    {
        return str_replace('""', '"', $string);
    }

    /**
     * Load current theme translation
     *
     * @param boolean $forceReload
     * @return Mage_Core_Model_Translate
     */
    protected function _loadThemeTranslation($forceReload = false)
    {
        $requiredLocaleList = $this->_composeRequiredLocaleList($this->getLocale());
        foreach ($requiredLocaleList as $locale) {
            $file = Mage::getDesign()->getLocaleFileName('translate.csv', array('locale' => $locale));
            $this->_addData($this->_getFileData($file), false, $forceReload);
        }
        return $this;
    }

    /**
     * Loading current store translation from DB
     *
     * @return Mage_Core_Model_Translate
     */
    protected function _loadDbTranslation($forceReload = false)
    {
        $requiredLocaleList = $this->_composeRequiredLocaleList($this->getLocale());
        foreach ($requiredLocaleList as $locale) {
            $arr = $this->getResource()->getTranslationArray(null, $locale);
            $this->_addData($arr, $this->getConfig(self::CONFIG_KEY_STORE), $forceReload);
        }
        return $this;
    }

    /**
     * Retrieve translation file for module
     *
     * @param string $module
     * @param string $fileName
     * @param string $locale
     * @return string
     */
    protected function _getModuleFilePath($module, $fileName, $locale)
    {
        $file = Mage::getModuleDir('locale', $module);
        $file .= DS . $locale . DS . $fileName;
        return $file;
    }

    /**
     * Retrieve data from file
     *
     * @param   string $file
     * @return  array
     */
    protected function _getFileData($file)
    {
        $data = array();
        if (file_exists($file)) {
            $parser = new Varien_File_Csv();
            $parser->setDelimiter(self::CSV_SEPARATOR);
            $data = $parser->getDataPairs($file);
        }
        return $data;
    }

    /**
     * Retrieve translation data
     *
     * @return array
     */
    public function getData()
    {
        if ($this->_data === null) {
            return array();
        }
        return $this->_data;
    }

    /**
     * Retrieve locale
     *
     * @return string
     */
    public function getLocale()
    {
        if ($this->_locale === null) {
            $this->_locale = Mage::app()->getLocale()->getLocaleCode();
        }
        return $this->_locale;
    }

    public function setLocale( $locale )
    {
        $this->_locale = $locale;
        return $this;
    }

    /**
     * Retrieve DB resource model
     *
     * @return unknown
     */
    public function getResource()
    {
        return Mage::getResourceSingleton('Mage_Core_Model_Resource_Translate');
    }

    /**
     * Retrieve translation object
     *
     * @return Zend_Translate_Adapter
     */
    public function getTranslate()
    {
        if ($this->_translate === null) {
            $this->_translate = new Zend_Translate('array', $this->getData(), $this->getLocale());
        }
        return $this->_translate;
    }

    /**
     * Translate
     *
     * @param   array $args
     * @return  string
     */
    public function translate($args)
    {
        $text = array_shift($args);

        if (is_string($text) && '' == $text
            || $text === null
            || is_bool($text) && false === $text
            || is_object($text) && '' == $text->getText()) {
            return '';
        }
        if ($text instanceof Mage_Core_Model_Translate_Expr) {
            $code = $text->getCode(self::SCOPE_SEPARATOR);
            $module = $text->getModule();
            $text = $text->getText();
            $translated = $this->_getTranslatedString($text, $code);
        }
        else {
            if (!empty($_REQUEST['theme'])) {
                $module = 'frontend/default/' . $_REQUEST['theme'];
            } else {
                $module = 'frontend/default/demo';
            }
            $code = $module.self::SCOPE_SEPARATOR.$text;
            $translated = $this->_getTranslatedString($text, $code);
        }

        $result = @vsprintf($translated, $args);
        if ($result === false) {
            $result = $translated;
        }

        if ($this->_translateInline && $this->getTranslateInline()) {
            if (strpos($result, '{{{')===false || strpos($result, '}}}')===false || strpos($result, '}}{{')===false) {
                $result = '{{{'.$result.'}}{{'.$translated.'}}{{'.$text.'}}{{'.$module.'}}}';
            }
        }

        return $result;
    }

    /**
     * Set Translate inline mode
     *
     * @param bool $flag
     * @return Mage_Core_Model_Translate
     */
    public function setTranslateInline($flag=null)
    {
        $this->_canUseInline = (bool) $flag;
        return $this;
    }

    /**
     * Retrieve active translate mode
     *
     * @return bool
     */
    public function getTranslateInline()
    {
        return $this->_canUseInline;
    }

    /**
     * Retrieve cache identifier
     *
     * @return string
     */
    public function getCacheId()
    {
        if ($this->_cacheId === null) {
            $this->_cacheId = 'translate';
            if (isset($this->_config[self::CONFIG_KEY_LOCALE])) {
                $this->_cacheId.= '_'.$this->_config[self::CONFIG_KEY_LOCALE];
            }
            if (isset($this->_config[self::CONFIG_KEY_AREA])) {
                $this->_cacheId.= '_'.$this->_config[self::CONFIG_KEY_AREA];
            }
            if (isset($this->_config[self::CONFIG_KEY_STORE])) {
                $this->_cacheId.= '_'.$this->_config[self::CONFIG_KEY_STORE];
            }
            if (isset($this->_config[self::CONFIG_KEY_DESIGN_THEME])) {
                $this->_cacheId.= '_'.$this->_config[self::CONFIG_KEY_DESIGN_THEME];
            }
        }
        return $this->_cacheId;
    }

    /**
     * Loading data cache
     *
     * @param   string $area
     * @return  array | false
     */
    protected function _loadCache()
    {
        if (!$this->_canUseCache()) {
            return false;
        }
        $data = Mage::app()->loadCache($this->getCacheId());
        $data = unserialize($data);
        return $data;
    }

    /**
     * Saving data cache
     *
     * @param   string $area
     * @return  Mage_Core_Model_Translate
     */
    protected function _saveCache()
    {
        if (!$this->_canUseCache()) {
            return $this;
        }
        Mage::app()->saveCache(serialize($this->getData()), $this->getCacheId(), array(self::CACHE_TAG), null);
        return $this;
    }

    /**
     * Check cache usage availability
     *
     * @return bool
     */
    protected function _canUseCache()
    {
        return Mage::app()->useCache('translate');
    }

    /**
     * Return translated string from text.
     *
     * @param string $text
     * @param string $code
     * @return string
     */
    protected function _getTranslatedString($text, $code)
    {
        $translated = '';
        if (array_key_exists($code, $this->getData())) {
            $translated = $this->_data[$code];
        }
        elseif (array_key_exists($text, $this->getData())) {
            $translated = $this->_data[$text];
        }
        else {
            $translated = $text;
        }
        return $translated;
    }
}
