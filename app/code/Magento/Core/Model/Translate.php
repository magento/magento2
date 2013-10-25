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

namespace Magento\Core\Model;

/**
 * Translate model
 *
 * @todo Remove this suppression when jira entry MAGETWO-8296 is completed.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Translate
{
    /**
     * CSV separator
     */
    const CSV_SEPARATOR     = ',';

    /**
     * Scope separator
     */
    const SCOPE_SEPARATOR   = '::';

    /**
     * Configuration area key
     */
    const CONFIG_KEY_AREA   = 'area';

    /**
     * Configuration locale kay
     */
    const CONFIG_KEY_LOCALE = 'locale';

    /**
     * Configuration store key
     */
    const CONFIG_KEY_STORE  = 'store';

    /**
     * Configuration theme key
     */
    const CONFIG_KEY_DESIGN_THEME   = 'theme';

    /**
     * Default translation string
     */
    const DEFAULT_STRING = 'Translate String';

    /**
     * Locale code
     *
     * @var string
     */
    protected $_localeCode;

    /**
     * Translation object
     *
     * @var \Zend_Translate_Adapter
     */
    protected $_translate;

    /**
     * Translator configuration array
     *
     * @var array
     */
    protected $_config;

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
     * @var \Magento\Core\Model\Translate\InlineInterface
     */
    protected $_inlineInterface;

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
     * @var \Magento\View\DesignInterface
     */
    protected $_viewDesign;

    /**
     * @var \Magento\Core\Model\Translate\Factory
     */
    protected $_translateFactory;

    /**
     * @var \Magento\Cache\FrontendInterface $cache
     */
    private $_cache;

    /**
     * @var \Magento\Core\Model\View\FileSystem
     */
    protected $_viewFileSystem;

    /**
     * @var \Magento\Phrase\Renderer\Placeholder
     */
    protected $_placeholderRender;

    /**
     * @var \Magento\App\ModuleList
     */
    protected $_moduleList;

    /**
     * @var \Magento\Core\Model\Config\Modules\Reader
     */
    protected $_modulesReader;

    /**
     * @var \Magento\Core\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * @var \Magento\Core\Model\Resource\Translate
     */
    protected $_translateResource;

    /**
     * @var \Magento\Core\Model\App
     */
    protected $_app;

    /**
     * @param \Magento\View\DesignInterface $viewDesign
     * @param \Magento\Core\Model\Locale\Hierarchy\Config $config
     * @param \Magento\Core\Model\Translate\Factory $translateFactory
     * @param \Magento\Cache\FrontendInterface $cache
     * @param \Magento\Core\Model\View\FileSystem $viewFileSystem
     * @param \Magento\Phrase\Renderer\Placeholder $placeholderRender
     * @param \Magento\App\ModuleList $moduleList
     * @param \Magento\Core\Model\Config\Modules\Reader $modulesReader
     * @param \Magento\Core\Model\Config $coreConfig
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\Core\Model\Resource\Translate $translate
     * @param \Magento\Core\Model\App $app
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\View\DesignInterface $viewDesign,
        \Magento\Core\Model\Locale\Hierarchy\Config $config,
        \Magento\Core\Model\Translate\Factory $translateFactory,
        \Magento\Cache\FrontendInterface $cache,
        \Magento\Core\Model\View\FileSystem $viewFileSystem,
        \Magento\Phrase\Renderer\Placeholder $placeholderRender,
        \Magento\App\ModuleList $moduleList,
        \Magento\Core\Model\Config\Modules\Reader $modulesReader,
        \Magento\Core\Model\Config $coreConfig,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\Core\Model\Resource\Translate $translate,
        \Magento\Core\Model\App $app
    ) {
        $this->_viewDesign = $viewDesign;
        $this->_localeHierarchy = $config->getHierarchy();
        $this->_translateFactory = $translateFactory;
        $this->_cache = $cache;
        $this->_viewFileSystem = $viewFileSystem;
        $this->_placeholderRender = $placeholderRender;
        $this->_moduleList = $moduleList;
        $this->_modulesReader = $modulesReader;
        $this->_coreConfig = $coreConfig;
        $this->_storeManager = $storeManager;
        $this->_translateResource = $translate;
        $this->_app = $app;
    }

    /**
     * Initialization translation data
     *
     * @param string $area
     * @param \Magento\Object $initParams
     * @param bool $forceReload
     * @return \Magento\Core\Model\Translate
     */
    public function init($area, $initParams = null, $forceReload = false)
    {
        $this->setConfig(array(self::CONFIG_KEY_AREA => $area));

        $this->_translateInline = $this->getInlineObject($initParams)->isAllowed(
            $area == \Magento\Backend\Helper\Data::BACKEND_AREA_CODE ? 'admin' : null);

        if (!$forceReload) {
            $this->_data = $this->_loadCache();
            if ($this->_data !== false) {
                return $this;
            }
        }

        $this->_data = array();

        foreach ($this->_moduleList->getModules() as $module) {
            $this->_loadModuleTranslation($module['name']);
        }

        $this->_loadThemeTranslation($forceReload);
        $this->_loadDbTranslation($forceReload);

        if (!$forceReload) {
            $this->_saveCache();
        }

        return $this;
    }

    /**
     * Initialize configuration
     *
     * @param   array $config
     * @return  \Magento\Core\Model\Translate
     */
    public function setConfig($config)
    {
        $this->_config = $config;
        if (!isset($this->_config[self::CONFIG_KEY_LOCALE])) {
            $this->_config[self::CONFIG_KEY_LOCALE] = $this->getLocale();
        }
        if (!isset($this->_config[self::CONFIG_KEY_STORE])) {
            $this->_config[self::CONFIG_KEY_STORE] = $this->_storeManager->getStore()->getId();
        }
        if (!isset($this->_config[self::CONFIG_KEY_DESIGN_THEME])) {
            $this->_config[self::CONFIG_KEY_DESIGN_THEME] = $this->_viewDesign->getDesignTheme()->getId();
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
     * Determine if translation is enabled and allowed.
     *
     * @param mixed $store
     * @return bool
     */
    public function isAllowed($store = null)
    {
        /** @todo see jira entry MAGETWO-8296 */
        return $this->getInlineObject()->isAllowed($store);
    }

    /**
     * Parse and save edited translate
     *
     * @param array $translate
     * @return \Magento\Core\Model\Translate\InlineInterface
     */
    public function processAjaxPost($translate)
    {
        /** @var \Magento\Core\Model\Cache\TypeListInterface $cacheTypeList */
        $cacheTypeList = $this->_translateFactory->create(array(), 'Magento\Core\Model\Cache\TypeListInterface');
        $cacheTypeList->invalidate(\Magento\Core\Model\Cache\Type\Translate::TYPE_IDENTIFIER);
        /** @var $parser \Magento\Core\Model\Translate\InlineParser */
        $parser = $this->_translateFactory->create(array(), 'Magento\Core\Model\Translate\InlineParser');
        $parser->processAjaxPost($translate, $this->getInlineObject());
    }

    /**
     * Replace translation templates with HTML fragments
     *
     * @param array|string $body
     * @param bool $isJson
     * @return \Magento\Core\Model\Translate\InlineInterface
     */
    public function processResponseBody(&$body,
        $isJson = \Magento\Core\Model\Translate\InlineParser::JSON_FLAG_DEFAULT_STATE
    ) {
        return $this->getInlineObject()->processResponseBody($body, $isJson);
    }

    /**
     * Load data from module translation files
     *
     * @param string $moduleName
     * @return \Magento\Core\Model\Translate
     */
    protected function _loadModuleTranslation($moduleName)
    {
        $requiredLocaleList = $this->_composeRequiredLocaleList($this->getLocale());
        foreach ($requiredLocaleList as $locale) {
            $moduleFilePath = $this->_getModuleTranslationFile($moduleName, $locale);
            $this->_addData($this->_getFileData($moduleFilePath));
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
     * @param string|bool $scope
     * @param boolean $forceReload
     * @return \Magento\Core\Model\Translate
     */
    protected function _addData($data, $scope = false, $forceReload = false)
    {
        foreach ($data as $key => $value) {
            if ($key === $value) {
                continue;
            }
            $key    = $this->_prepareDataString($key);
            $value  = $this->_prepareDataString($value);
            if ($scope && isset($this->_dataScope[$key]) && !$forceReload ) {
                /**
                 * Checking previous value
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
            } else {
                $this->_data[$key] = $value;
                $this->_dataScope[$key]= $scope;
            }
        }
        return $this;
    }

    /**
     * Prepare data string
     *
     * @param string $string
     * @return string
     */
    protected function _prepareDataString($string)
    {
        return str_replace('""', '"', $string);
    }

    /**
     * Load current theme translation
     *
     * @param boolean $forceReload
     * @return \Magento\Core\Model\Translate
     */
    protected function _loadThemeTranslation($forceReload = false)
    {
        if (!$this->_config[self::CONFIG_KEY_DESIGN_THEME]) {
            return $this;
        }

        $requiredLocaleList = $this->_composeRequiredLocaleList($this->getLocale());
        foreach ($requiredLocaleList as $locale) {
            $file = $this->_getThemeTranslationFile($locale);
            $this->_addData(
                $this->_getFileData($file),
                self::CONFIG_KEY_DESIGN_THEME . $this->_config[self::CONFIG_KEY_DESIGN_THEME],
                $forceReload
            );
        }
        return $this;
    }

    /**
     * Loading current store translation from DB
     *
     * @param boolean $forceReload
     * @return \Magento\Core\Model\Translate
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
     * @param string $moduleName
     * @param string $locale
     * @return string
     */
    protected function _getModuleTranslationFile($moduleName, $locale)
    {
        $file = $this->_modulesReader->getModuleDir(\Magento\App\Dir::LOCALE, $moduleName);
        $file .= DS . $locale . '.csv';
        return $file;
    }

    /**
     * Retrieve translation file for theme
     *
     * @param string $locale
     * @return string
     */
    protected function _getThemeTranslationFile($locale)
    {
        return $this->_viewFileSystem->getFilename(\Magento\App\Dir::LOCALE . DS . $locale . '.csv');
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
            $parser = new \Magento\File\Csv();
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
        if (is_null($this->_data)) {
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
        if (null === $this->_localeCode) {
            $this->_localeCode = $this->_app->getLocale()->getLocaleCode();
        }
        return $this->_localeCode;
    }

    /**
     * Set locale
     *
     * @param string $locale
     * @return \Magento\Core\Model\Translate
     */
    public function setLocale($locale)
    {
        $this->_localeCode = $locale;
        return $this;
    }

    /**
     * Retrieve DB resource model
     *
     * @return \Magento\Core\Model\Resource\Translate
     */
    public function getResource()
    {
        return $this->_translateResource;
    }

    /**
     * Retrieve translation object
     *
     * @return \Zend_Translate_Adapter
     */
    public function getTranslate()
    {
        if (null === $this->_translate) {
            $this->_translate = new \Zend_Translate('array', $this->getData(), $this->getLocale());
        }
        return $this->_translate;
    }

    /**
     * Translate
     *
     * @param array $args
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function translate($args)
    {
        $text = array_shift($args);

        if ($this->_isEmptyTranslateArg($text)) {
            return '';
        }

        if (!empty($_REQUEST['theme'])) {
            $module = self::CONFIG_KEY_DESIGN_THEME . $_REQUEST['theme']['theme_title'];
        } else {
            $module = self::CONFIG_KEY_DESIGN_THEME . $this->_config[self::CONFIG_KEY_DESIGN_THEME];
        }
        $code = $module . self::SCOPE_SEPARATOR . $text;
        $translated = $this->_getTranslatedString($text, $code);
        $result = $this->_placeholderRender->render($translated, $args);

        if ($this->_translateInline && $this->getTranslateInline()) {
            if (strpos($result, '{{{') === false
                || strpos($result, '}}}') === false
                || strpos($result, '}}{{') === false
            ) {
                $result = '{{{' . $result . '}}{{' . $translated . '}}{{' . $text . '}}{{' . $module . '}}}';
            }
        }
        return $result;
    }

    /**
     * Check is empty translate argument
     *
     * @param mixed $text
     * @return bool
     */
    protected function _isEmptyTranslateArg($text)
    {
        if (is_object($text) && is_callable(array($text, 'getText'))) {
            $text = $text->getText();
        }
        return empty($text);
    }

    /**
     * Set Translate inline mode
     *
     * @param bool $flag
     * @return \Magento\Core\Model\Translate
     */
    public function setTranslateInline($flag = false)
    {
        $this->_canUseInline = $flag;
        return $this;
    }

    /**
     * Retrieve active translate mode
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
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
        if (is_null($this->_cacheId)) {
            $this->_cacheId = \Magento\Core\Model\Cache\Type\Translate::TYPE_IDENTIFIER;
            if (isset($this->_config[self::CONFIG_KEY_LOCALE])) {
                $this->_cacheId .= '_' . $this->_config[self::CONFIG_KEY_LOCALE];
            }
            if (isset($this->_config[self::CONFIG_KEY_AREA])) {
                $this->_cacheId .= '_' . $this->_config[self::CONFIG_KEY_AREA];
            }
            if (isset($this->_config[self::CONFIG_KEY_STORE])) {
                $this->_cacheId .= '_' . $this->_config[self::CONFIG_KEY_STORE];
            }
            if (isset($this->_config[self::CONFIG_KEY_DESIGN_THEME])) {
                $this->_cacheId .= '_' . $this->_config[self::CONFIG_KEY_DESIGN_THEME];
            }
        }
        return $this->_cacheId;
    }

    /**
     * Loading data cache
     *
     * @return array|bool
     */
    protected function _loadCache()
    {
        $data = $this->_cache->load($this->getCacheId());
        if ($data) {
            $data = unserialize($data);
        }
        return $data;
    }

    /**
     * Saving data cache
     *
     * @return \Magento\Core\Model\Translate
     */
    protected function _saveCache()
    {
        $this->_cache->save(serialize($this->getData()), $this->getCacheId(), array(), false);
        return $this;
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
        if (array_key_exists($code, $this->getData())) {
            $translated = $this->_data[$code];
        } elseif (array_key_exists($text, $this->getData())) {
            $translated = $this->_data[$text];
        } else {
            $translated = $text;
        }
        return $translated;
    }

    /**
     * Returns the translate interface object.
     *
     * @param \Magento\Object $initParams
     * @return \Magento\Core\Model\Translate\InlineInterface
     */
    private function getInlineObject($initParams = null)
    {
        if (null === $this->_inlineInterface) {
            if ($initParams === null) {
                $this->_inlineInterface = $this->_translateFactory->create();
            } else {
                $this->_inlineInterface = $this->_translateFactory
                    ->create($initParams->getParams(), $initParams->getInlineType());
            }
        }
        return $this->_inlineInterface;
    }
}
