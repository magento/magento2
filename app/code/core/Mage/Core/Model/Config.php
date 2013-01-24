<?php
/**
 * Core configuration class
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mage_Core_Model_Config extends Mage_Core_Model_Config_Base
{
    /**
     * Configuration cache tag
     */
    const CACHE_TAG = 'CONFIG';

    /**
     * Stores configuration scope
     */
    const SCOPE_STORES = 'stores';

    /**
     * Websites configuration scope
     */
    const SCOPE_WEBSITES = 'websites';

    /**@+
     * Option key names
     */
    const OPTION_LOCAL_CONFIG_EXTRA_FILE = 'local_config';
    const OPTION_LOCAL_CONFIG_EXTRA_DATA = 'local_config_extra_data';
    /**@-*/

    /**
     * Local configuration file
     */
    const LOCAL_CONFIG_FILE = 'local.xml';

    /**
     * Application installation date
     */
    const XML_PATH_INSTALL_DATE = 'global/install/date';

    /**
     * Configuration template for the application installation date
     */
    const CONFIG_TEMPLATE_INSTALL_DATE = '<config><global><install><date>%s</date></install></global></config>';

    /**
     * Flag which allow use cache logic
     *
     * @var bool
     */
    protected $_useCache = false;

    /**
     * Instructions for spitting config cache
     * array(
     *      $sectionName => $recursionLevel
     * )
     * Recursion level provide availability cache sub nodes separately
     *
     * @var array
     */
    protected $_cacheSections = array(
        'admin'     => 0,
        'adminhtml' => 0,
        'crontab'   => 0,
        'install'   => 0,
        self::SCOPE_STORES => 1,
        self::SCOPE_WEBSITES => 0
    );

    /**
     * Loaded Configuration by cached sections
     *
     * @var array
     */
    protected $_cacheLoadedSections = array();

    /**
     * Configuration options
     *
     * @var Mage_Core_Model_Config_Options
     */
    protected $_options;

    /**
     * Storage for generated class names
     *
     * @var array
     */
    protected $_classNameCache = array();

    /**
     * Storage for generated block class names
     *
     * @var array
     */
    protected $_blockClassNameCache = array();

    /**
     * Storage of validated secure urls
     *
     * @var array
     */
    protected $_secureUrlCache = array();

    /**
     * System environment server variables
     *
     * @var array
     */
    protected $_distroServerVars;

    /**
     * Array which is using for replace placeholders of server variables
     *
     * @var array
     */
    protected $_substServerVars;

    /**
     * Resource model
     * Used for operations with DB
     *
     * @var Mage_Core_Model_Resource_Config
     */
    protected $_resourceModel;

    /**
     * Configuration data model
     *
     * @var Mage_Core_Model_Config_Data
     */
    protected $_configDataModel;

    /**
     * Configuration for events by area
     *
     * @var array
     */
    protected $_eventAreas;

    /**
     * Flag cache for existing or already created directories
     *
     * @var array
     */
    protected $_dirExists = array();

    /**
     * Flach which allow using cache for config initialization
     *
     * @var bool
     */
    protected $_allowCacheForInit = true;

    /**
     * Property used during cache save process
     *
     * @var array
     */
    protected $_cachePartsForSave = array();

    /**
     * Empty configuration object for loading and merging configuration parts
     *
     * @var Mage_Core_Model_Config_Base
     */
    protected $_prototype;

    /**
     * Whether local configuration is loaded or not
     *
     * @var bool
     */
    protected $_isLocalConfigLoaded = false;

    /**
     * Active modules array per namespace
     *
     * @var array
     */
    private $_moduleNamespaces = null;

    /**
     * Modules allowed to load
     * If empty - all modules are allowed
     *
     * @var array
     */
    protected $_allowedModules = array();

    /**
     * Areas allowed to use
     *
     * @var array
     */
    protected $_allowedAreas = null;

    /**
     * Paths to module's directories (etc, sql, locale etc)
     *
     * @var array
     */
    protected $_moduleDirs = array();

    /**
     * Cache for declared modules to prevent loading modules' config twice
     *
     * @var array
     */
    protected $_modulesCache = array();

    /**
     * Current area code
     *
     * @var string
     */
    protected $_currentAreaCode = null;

    /**
     * Object manager
     *
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * Application installation timestamp
     *
     * @var int|null
     */
    protected $_installDate;

    /**
     * Class construct
     *
     * @param Magento_ObjectManager $objectManager
     * @param mixed $sourceData
     */
    public function __construct(Magento_ObjectManager $objectManager, $sourceData = null)
    {
        $this->_objectManager = $objectManager;
        $this->setCacheId('config_global');
        $options = $sourceData;
        if (!is_array($options)) {
            $options = array($options);
        }
        $this->_options = $this->_objectManager->create('Mage_Core_Model_Config_Options', array('data' => $options));
        $this->_prototype = $this->_objectManager->create('Mage_Core_Model_Config_Base');
        $this->_prototype->loadString('<config/>');
        $this->_cacheChecksum = null;
        parent::__construct($sourceData);
    }

    /**
     * Get config resource model
     *
     * @return Mage_Core_Model_Resource_Config
     */
    public function getResourceModel()
    {
        if (is_null($this->_resourceModel)) {
            $this->_resourceModel = Mage::getResourceModel('Mage_Core_Model_Resource_Config');
        }
        return $this->_resourceModel;
    }

    /**
     * Get configuration data model
     *
     * @return Mage_Core_Model_Config_Data
     */
    public function getConfigDataModel()
    {
        if ($this->_configDataModel === null) {
            $this->_configDataModel = Mage::getModel('Mage_Core_Model_Config_Data');
        }
        return $this->_configDataModel;
    }

    /**
     * Get configuration options object
     *
     * @return Mage_Core_Model_Config_Options
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Set configuration options
     *
     * @param array $options
     * @return Mage_Core_Model_Config
     */
    public function setOptions($options)
    {
        if (is_array($options)) {
            $this->getOptions()->addData($options);
        }
        return $this;
    }

    /**
     * Initialization of core configuration
     *
     * @param array $options
     * @return Mage_Core_Model_Config
     */
    public function init($options = array())
    {
        $this->setCacheChecksum(null);
        $this->_cacheLoadedSections = array();
        $this->setOptions($options);
        $this->loadBase();

        $cacheLoad = $this->loadModulesCache();
        if ($cacheLoad) {
            return $this;
        }
        $this->loadModules();
        $this->loadDb();
        $this->loadLocales();
        $this->saveCache();
        return $this;
    }

    /**
     * Load base configuration
     *
     * @return Mage_Core_Model_Config
     */
    public function loadBase()
    {
        $etcDir = $this->getOptions()->getEtcDir();
        if (!$this->getNode()) {
            $this->loadString('<config/>');
        }
        // 1. app/etc/*.xml (except local config)
        foreach (scandir($etcDir) as $filename) {
            if ('.' == $filename || '..' == $filename || '.xml' != substr($filename, -4)
                || self::LOCAL_CONFIG_FILE == $filename
            ) {
                continue;
            }
            $baseConfigFile = $etcDir . DIRECTORY_SEPARATOR . $filename;
            $baseConfig = clone $this->_prototype;
            $baseConfig->loadFile($baseConfigFile);
            $this->extend($baseConfig);
        }
        // 2. local configuration
        $this->_loadLocalConfig();
        return $this;
    }

    /**
     * Load local configuration (part of the base configuration)
     */
    protected function _loadLocalConfig()
    {
        $etcDir = $this->getOptions()->getEtcDir();
        $localConfigParts = array();

        $localConfigFile = $etcDir . DIRECTORY_SEPARATOR . self::LOCAL_CONFIG_FILE;
        if (file_exists($localConfigFile)) {
            // 1. app/etc/local.xml
            $localConfig = clone $this->_prototype;
            $localConfig->loadFile($localConfigFile);
            $localConfigParts[] = $localConfig;

            // 2. app/etc/<dir>/<file>.xml
            $localConfigExtraFile = $this->getOptions()->getData(self::OPTION_LOCAL_CONFIG_EXTRA_FILE);
            if (preg_match('/^[a-z\d_-]+\/[a-z\d_-]+\.xml$/', $localConfigExtraFile)) {
                $localConfigExtraFile = $etcDir . DIRECTORY_SEPARATOR . $localConfigExtraFile;
                $localConfig = clone $this->_prototype;
                $localConfig->loadFile($localConfigExtraFile);
                $localConfigParts[] = $localConfig;
            }
        }

        // 3. extra local configuration string
        $localConfigExtraData = $this->getOptions()->getData(self::OPTION_LOCAL_CONFIG_EXTRA_DATA);
        if ($localConfigExtraData) {
            $localConfig = clone $this->_prototype;
            $localConfig->loadString($localConfigExtraData);
            $localConfigParts[] = $localConfig;
        }

        if ($localConfigParts) {
            foreach ($localConfigParts as $oneConfigPart) {
                $this->extend($oneConfigPart);
            }
            $this->_isLocalConfigLoaded = true;
            $this->_loadInstallDate();
        }
    }

    /**
     * Load application installation date
     */
    protected function _loadInstallDate()
    {
        $installDateNode = $this->getNode(self::XML_PATH_INSTALL_DATE);
        if ($installDateNode) {
            $this->_installDate = strtotime((string)$installDateNode);
        }
    }

    /**
     * Retrieve application installation date as a timestamp or NULL, if it has not been installed yet
     *
     * @return int|null
     */
    public function getInstallDate()
    {
        return $this->_installDate;
    }

    /**
     * Load locale configuration from locale configuration files
     *
     * @return Mage_Core_Model_Config
     */
    public function loadLocales()
    {
        $localeDir = $this->getOptions()->getLocaleDir();
        $files = glob($localeDir . DS . '*' . DS . 'config.xml');

        if (is_array($files) && !empty($files)) {
            foreach ($files as $file) {
                $merge = clone $this->_prototype;
                $merge->loadFile($file);
                $this->extend($merge);
            }
        }
        return $this;
    }

    /**
     * Load cached modules and locale configuration
     *
     * @return bool
     */
    public function loadModulesCache()
    {
        if ($this->getInstallDate()) {
            if ($this->_canUseCacheForInit()) {
                Magento_Profiler::start('init_modules_config_cache');
                $loaded = $this->loadCache();
                Magento_Profiler::stop('init_modules_config_cache');
                if ($loaded) {
                    $this->_useCache = true;
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Load modules configuration
     *
     * @return Mage_Core_Model_Config
     */
    public function loadModules()
    {
        Magento_Profiler::start('config');
        Magento_Profiler::start('load_modules');
        $this->_loadDeclaredModules();

        Magento_Profiler::start('load_modules_configuration');
        $resourceConfig = sprintf('config.%s.xml', $this->getResourceConnectionModel('core'));
        $this->loadModulesConfiguration(array('config.xml',$resourceConfig), $this);
        Magento_Profiler::stop('load_modules_configuration');

        // Prevent local configuration overriding
        $this->_loadLocalConfig();

        $this->applyExtends();
        Magento_Profiler::stop('load_modules');
        Magento_Profiler::stop('config');
        return $this;
    }

    /**
     * Check if local configuration (DB connection, etc) is loaded
     *
     * @return bool
     */
    public function isLocalConfigLoaded()
    {
        return $this->_isLocalConfigLoaded;
    }

    /**
     * Load config data from DB
     *
     * @return Mage_Core_Model_Config
     */
    public function loadDb()
    {
        Magento_Profiler::start('config');
        if ($this->getInstallDate()) {
            Magento_Profiler::start('load_db');
            $dbConf = $this->getResourceModel();
            $dbConf->loadToXml($this);
            Magento_Profiler::stop('load_db');
        }
        Magento_Profiler::stop('config');
        return $this;
    }

    /**
     * Reinitialize configuration
     *
     * @param   array $options
     * @return  Mage_Core_Model_Config
     */
    public function reinit($options = array())
    {
        $this->_allowCacheForInit = false;
        $this->_useCache = false;
        return $this->init($options);
    }

    /**
     * Check if cache can be used for config initialization
     *
     * @return bool
     */
    protected function _canUseCacheForInit()
    {
        return Mage::app()->useCache('config') && $this->_allowCacheForInit
            && !$this->_loadCache($this->_getCacheLockId());
    }

    /**
     * Retrieve cache object
     *
     * @return Zend_Cache_Frontend_File
     */
    public function getCache()
    {
        return Mage::app()->getCache();
    }

    /**
     * Get lock flag cache identifier
     *
     * @return string
     */
    protected function _getCacheLockId()
    {
        return $this->getCacheId() . '.lock';
    }

    /**
     * Save configuration cache
     *
     * @param   array $tags cache tags
     * @return  Mage_Core_Model_Config
     */
    public function saveCache($tags=array())
    {
        if (!Mage::app()->useCache('config')) {
            return $this;
        }
        if (!in_array(self::CACHE_TAG, $tags)) {
            $tags[] = self::CACHE_TAG;
        }
        $cacheLockId = $this->_getCacheLockId();
        if ($this->_loadCache($cacheLockId)) {
            return $this;
        }

        if (!empty($this->_cacheSections)) {
            $xml = clone $this->_xml;
            foreach ($this->_cacheSections as $sectionName => $level) {
                $this->_saveSectionCache($this->getCacheId(), $sectionName, $xml, $level, $tags);
                unset($xml->$sectionName);
            }
            $this->_cachePartsForSave[$this->getCacheId()] = $xml->asNiceXml('', false);
        } else {
            return parent::saveCache($tags);
        }

        $this->_saveCache(time(), $cacheLockId, array(), 60);
        $this->removeCache();
        foreach ($this->_cachePartsForSave as $cacheId => $cacheData) {
            $this->_saveCache($cacheData, $cacheId, $tags, $this->getCacheLifetime());
        }
        unset($this->_cachePartsForSave);
        $this->_removeCache($cacheLockId);
        return $this;
    }

    /**
     * Save cache of specified
     *
     * @param   string $idPrefix cache id prefix
     * @param   string $sectionName
     * @param   Varien_Simplexml_Element $source
     * @param   int $recursionLevel
     * @param   array $tags
     * @return  Mage_Core_Model_Config
     */
    protected function _saveSectionCache($idPrefix, $sectionName, $source, $recursionLevel = 0, $tags = array())
    {
        if ($source && $source->$sectionName) {
            $cacheId = $idPrefix . '_' . $sectionName;
            if ($recursionLevel > 0) {
                foreach (array_keys((array)$source->$sectionName->children()) as $subSectionName) {
                    $this->_saveSectionCache(
                        $cacheId, $subSectionName, $source->$sectionName, $recursionLevel - 1, $tags
                    );
                }
            }
            $this->_cachePartsForSave[$cacheId] = $source->$sectionName->asNiceXml('', false);
        }
        return $this;
    }

    /**
     * Load config section cached data
     *
     * @param   string $sectionName
     * @return  Varien_Simplexml_Element
     */
    protected function _loadSectionCache($sectionName)
    {
        $cacheId = $this->getCacheId() . '_' . $sectionName;
        $xmlString = $this->_loadCache($cacheId);

        /**
         * If we can't load section cache (problems with cache storage)
         */
        if (!$xmlString) {
            $this->_useCache = false;
            $this->reinit($this->_options);
            return false;
        } else {
            $xml = simplexml_load_string($xmlString, $this->_elementClass);
            return $xml;
        }
    }

    /**
     * Load cached data by identifier
     *
     * @param   string $cacheId
     * @return  string
     */
    protected function _loadCache($cacheId)
    {
        return Mage::app()->loadCache($cacheId);
    }

    /**
     * Save cache data
     *
     * @param   string $data
     * @param   string $cacheId
     * @param   array $tags
     * @param   bool|int $lifetime
     * @return  Mage_Core_Model_Config
     */
    protected function _saveCache($data, $cacheId, $tags = array(), $lifetime = false)
    {
        return Mage::app()->saveCache($data, $cacheId, $tags, $lifetime);
    }

    /**
     * Clear cache data by id
     *
     * @param   string $cacheId
     * @return  Mage_Core_Model_Config
     */
    protected function _removeCache($cacheId)
    {
        return Mage::app()->removeCache($cacheId);
    }

    /**
     * Remove configuration cache
     *
     * @return Mage_Core_Model_Config
     */
    public function removeCache()
    {
        Mage::app()->cleanCache(array(self::CACHE_TAG));
        return parent::removeCache();
    }

    /**
     * Configuration cache clean process
     *
     * @return Mage_Core_Model_Config
     */
    public function cleanCache()
    {
        return $this->reinit();
    }

    /**
     * Getter for section configuration object
     *
     * @param array $path
     * @return Mage_Core_Model_Config_Element
     */
    protected function _getSectionConfig($path)
    {
        $section = $path[0];
        if (!isset($this->_cacheSections[$section])) {
            return false;
        }
        $sectionPath = array_slice($path, 0, $this->_cacheSections[$section]+1);
        $sectionKey = implode('_', $sectionPath);

        if (!isset($this->_cacheLoadedSections[$sectionKey])) {
            Magento_Profiler::start('init_config_section:' . $sectionKey, array('group' => 'init_config_section',
                'section_key' => $sectionKey));
            $this->_cacheLoadedSections[$sectionKey] = $this->_loadSectionCache($sectionKey);
            Magento_Profiler::stop('init_config_section:' . $sectionKey);
        }

        if ($this->_cacheLoadedSections[$sectionKey] === false) {
            return false;
        }
        return $this->_cacheLoadedSections[$sectionKey];
    }

    /**
     * Get node value from cached section data
     *
     * @param   array $path
     * @return  Mage_Core_Model_Config
     */
    public function getSectionNode($path)
    {
        $section    = $path[0];
        $config     = $this->_getSectionConfig($path);
        $path       = array_slice($path, $this->_cacheSections[$section] + 1);
        if ($config) {
            return $config->descend($path);
        }
        return false;
    }

    /**
     * Returns node found by the $path and scope info
     *
     * @param  string $path
     * @param  string $scope
     * @param  string|int $scopeCode
     * @return Mage_Core_Model_Config_Element
     */
    public function getNode($path = null, $scope = '', $scopeCode = null)
    {
        $path = $this->_getPathInScope($path, $scope, $scopeCode);

        /**
         * Check path cache loading
         */
        if ($this->_useCache && ($path !== null)) {
            $path    = explode('/', $path);
            $section = $path[0];
            if (isset($this->_cacheSections[$section])) {
                $res = $this->getSectionNode($path);
                if ($res !== false) {
                    return $res;
                }
            }
        }

        return parent::getNode($path);
    }

    /**
     * Get path for scope
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param null $path
     * @param string $scope
     * @param string|int $scopeCode
     * @return null|string
     */
    protected function _getPathInScope($path = null, $scope, $scopeCode)
    {
        if ($scope !== '') {
            if (('store' === $scope) || ('website' === $scope)) {
                $scope .= 's';
            }
            if ((Mage_Core_Model_Store::DEFAULT_CODE !== $scope) && is_int($scopeCode)) {
                $scopeCode = $this->_getScopeCode($scope, $scopeCode);
            }
            $path = $scope . ($scopeCode ? '/' . $scopeCode : '') . (empty($path) ? '' : '/' . $path);
        }
        return $path;
    }

    /**
     * Get scope code by id
     *
     * @param string $scope
     * @param int $scopeId
     * @return string
     */
    protected function _getScopeCode($scope, $scopeId)
    {
        $scopeCode = '';
        if (self::SCOPE_STORES == $scope) {
            $scopeCode = Mage::app()->getStore($scopeId)->getCode();
        } elseif (self::SCOPE_WEBSITES == $scope) {
            $scopeCode = Mage::app()->getWebsite($scopeId)->getCode();
        } else {
            Mage::throwException(Mage::helper('Mage_Core_Helper_Data')->__('Unknown scope "%s".', $scope));
        }
        return $scopeCode;
    }

    /**
     * Create node by $path and set its value.
     *
     * @param string $path separated by slashes
     * @param string $value
     * @param bool $overwrite
     * @return Varien_Simplexml_Config
     */
    public function setNode($path, $value, $overwrite = true)
    {
        if ($this->_useCache && ($path !== null)) {
            $sectionPath = explode('/', $path);
            $config = $this->_getSectionConfig($sectionPath);
            if ($config) {
                $sectionPath = array_slice($sectionPath, $this->_cacheSections[$sectionPath[0]]+1);
                $sectionPath = implode('/', $sectionPath);
                $config->setNode($sectionPath, $value, $overwrite);
            }
        }
        return parent::setNode($path, $value, $overwrite);
    }


    /**
     * Retrieve declared module file list
     *
     * @return array
     */
    protected function _getDeclaredModuleFiles()
    {
        $codeDir = $this->getOptions()->getCodeDir();
        $moduleFiles = glob($codeDir . DS . '*' . DS . '*' . DS . '*' . DS . 'etc' . DS . 'config.xml');

        if (!$moduleFiles) {
            return false;
        }

        $collectModuleFiles = array(
            'base'   => array(),
            'mage'   => array(),
            'custom' => array()
        );

        foreach ($moduleFiles as $v) {
            $name = explode(DIRECTORY_SEPARATOR, $v);
            $collection = $name[count($name) - 4];

            if ($collection == 'Mage') {
                $collectModuleFiles['mage'][] = $v;
            } else {
                $collectModuleFiles['custom'][] = $v;
            }
        }

        $etcDir = $this->getOptions()->getEtcDir();
        $additionalFiles = glob($etcDir . DS . 'modules' . DS . '*.xml');

        foreach ($additionalFiles as $v) {
            $collectModuleFiles['base'][] = $v;
        }

        return array_merge(
            $collectModuleFiles['mage'],
            $collectModuleFiles['custom'],
            $collectModuleFiles['base']
        );
    }

    /**
     * Add module(s) to allowed list
     *
     * @param  string|array $module
     * @return Mage_Core_Model_Config
     */
    public function addAllowedModules($module)
    {
        if (is_array($module)) {
            foreach ($module as $moduleName) {
                $this->addAllowedModules($moduleName);
            }
        } elseif (!in_array($module, $this->_allowedModules)) {
            $this->_allowedModules[] = $module;
        }

        return $this;
    }

    /**
     * Load declared modules configuration
     *
     * @return  Mage_Core_Model_Config
     */
    protected function _loadDeclaredModules()
    {
        Magento_Profiler::start('load_modules_files');
        $moduleFiles = $this->_getDeclaredModuleFiles();
        if (!$moduleFiles) {
            return $this;
        }
        Magento_Profiler::stop('load_modules_files');

        Magento_Profiler::start('load_modules_declaration');
        $unsortedConfig = new Mage_Core_Model_Config_Base('<config/>');
        $emptyConfig = new Mage_Core_Model_Config_Element('<config><modules/></config>');
        $declaredModules = array();
        foreach ($moduleFiles as $oneConfigFile) {
            $path = explode(DIRECTORY_SEPARATOR, $oneConfigFile);
            $moduleConfig = new Mage_Core_Model_Config_Base($oneConfigFile);
            $modules = $moduleConfig->getXpath('modules/*');
            if (!$modules) {
                continue;
            }
            $cPath = count($path);
            if ($cPath > 4) {
                $moduleName = $path[$cPath - 4] . '_' . $path[$cPath - 3];
                $this->_modulesCache[$moduleName] = $moduleConfig;
            }
            foreach ($modules as $module) {
                $moduleName = $module->getName();
                $isActive = (string)$module->active;
                if (isset($declaredModules[$moduleName])) {
                    $declaredModules[$moduleName]['active'] = $isActive;
                    continue;
                }
                $newModule = clone $emptyConfig;
                $newModule->modules->appendChild($module);
                $declaredModules[$moduleName] = array(
                    'active' => $isActive,
                    'module' => $newModule,
                );
            }
        }
        foreach ($declaredModules as $moduleName => $module) {
            if ($module['active'] == 'true') {
                $module['module']->modules->{$moduleName}->active = 'true';
                $unsortedConfig->extend(new Mage_Core_Model_Config_Base($module['module']));
            }
        }
        $sortedConfig = new Mage_Core_Model_Config_Module($unsortedConfig, $this->_allowedModules);

        $this->extend($sortedConfig);
        Magento_Profiler::stop('load_modules_declaration');
        return $this;
    }

    /**
     * Determine whether provided name begins from any available modules, according to namespaces priority
     * If matched, returns as the matched module "factory" name or a fully qualified module name
     *
     * @param string $name
     * @param bool $asFullModuleName
     * @return string
     */
    public function determineOmittedNamespace($name, $asFullModuleName = false)
    {
        $this->_initModulesPreNamespaces();

        $name = explode('_', strtolower($name));
        $partsNum = count($name);
        $defaultNamespaceFlag = false;
        foreach ($this->_moduleNamespaces as $namespaceName => $namespace) {
            // assume the namespace is omitted (default namespace only, which comes first)
            if ($defaultNamespaceFlag === false) {
                $defaultNamespaceFlag = true;
                $defaultNS = $namespaceName . '_' . $name[0];
                if (isset($namespace[$defaultNS])) {
                    return $asFullModuleName ? $namespace[$defaultNS] : $name[0]; // return omitted as well
                }
            }
            // assume namespace is qualified
            if (isset($name[1])) {
                $fullNS = $name[0] . '_' . $name[1];
                if (2 <= $partsNum && isset($namespace[$fullNS])) {
                    return $asFullModuleName ? $namespace[$fullNS] : $fullNS;
                }
            }
        }
        return '';
    }

    /**
     * Init modules per namespaces
     *
     * @return Mage_Core_Model_Config
     */
    protected function _initModulesPreNamespaces()
    {
        if (null === $this->_moduleNamespaces) {
            $this->_moduleNamespaces = array();
            foreach ($this->_xml->xpath('modules/*') as $moduleNode) {
                if ((string)$moduleNode->active == 'true') {
                    $moduleName = $moduleNode->getName();
                    $module = strtolower($moduleName);
                    $this->_moduleNamespaces[substr($module, 0, strpos($module, '_'))][$module] = $moduleName;
                }
            }
        }
        return $this;
    }

    /**
     * Iterate all active modules "etc" folders and combine data from
     * specified xml file name to one object
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param   string $fileName
     * @param   null|Mage_Core_Model_Config_Base $mergeToObject
     * @param   null|Mage_Core_Model_Config_Base $mergeModel
     * @return  Mage_Core_Model_Config_Base
     */
    public function loadModulesConfiguration($fileName, $mergeToObject = null, $mergeModel = null)
    {
        if ($mergeToObject === null) {
            $mergeToObject = clone $this->_prototype;
        }

        $mergeModel = $mergeModel === null ? clone $this->_prototype : $mergeModel;

        $modules = $this->getNode('modules')->children();
        foreach ($modules as $modName => $module) {
            if (!$module->is('active')) {
                continue;
            }
            if (!is_array($fileName)) {
                $fileName = array($fileName);
            }
            foreach ($fileName as $configFile) {
                if ($configFile == 'config.xml' && isset($this->_modulesCache[$modName])) {
                    $mergeToObject->extend($this->_modulesCache[$modName], true);
                    //Prevent overriding <active> node of module if it was redefined in etc/modules
                    $mergeToObject->extend(new Mage_Core_Model_Config_Base(
                        "<config><modules><{$modName}><active>true</active></{$modName}></modules></config>"), true);
                } else {
                    $configFilePath = $this->getModuleDir('etc', $modName) . DS . $configFile;
                    if ($mergeModel->loadFile($configFilePath)) {
                        $mergeToObject->extend($mergeModel, true);
                    }
                }
            }
        }
        unset($this->_modulesCache);
        return $mergeToObject;
    }

    /**
     * Go through all modules and find configuration files of active modules
     *
     * @param string $filename
     * @return array
     */
    public function getModuleConfigurationFiles($filename)
    {
        $result = array();
        $modules = $this->getNode('modules')->children();
        foreach ($modules as $moduleName => $module) {
            if ((!$module->is('active'))) {
                continue;
            }
            $file = $this->getModuleDir('etc', $moduleName) . DIRECTORY_SEPARATOR . $filename;
            if (file_exists($file)) {
                $result[] = $file;
            }
        }
        return $result;
    }
    /**
     * Retrieve temporary directory path
     *
     * @return string
     */
    public function getTempVarDir()
    {
        return $this->getOptions()->getVarDir();
    }

    /**
     * Get default server variables values
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return array
     */
    public function getDistroServerVars()
    {
        if (!$this->_distroServerVars) {

            if (isset($_SERVER['SCRIPT_NAME']) && isset($_SERVER['HTTP_HOST'])) {
                $secure = (!empty($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != 'off'))
                    || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443');
                $scheme = ($secure ? 'https' : 'http') . '://' ;

                $hostArr = explode(':', $_SERVER['HTTP_HOST']);
                $host = $hostArr[0];
                $port = '';
                if (isset($hostArr[1]) && (!$secure && $hostArr[1] != 80 || $secure && $hostArr[1] != 443)) {
                    $port = ':' . $hostArr[1];
                }
                $path = Mage::app()->getRequest()->getBasePath();

                $baseUrl = $scheme . $host . $port . rtrim($path, '/') . '/';
            } else {
                $baseUrl = 'http://localhost/';
            }

            $options = $this->getOptions();
            $this->_distroServerVars = array(
                'root_dir'  => $options->getBaseDir(),
                'app_dir'   => $options->getAppDir(),
                'var_dir'   => $options->getVarDir(),
                'base_url'  => $baseUrl,
            );

            foreach ($this->_distroServerVars as $k => $v) {
                $this->_substServerVars['{{' . $k . '}}'] = $v;
            }
        }
        return $this->_distroServerVars;
    }

    /**
     * Replace distro vars with values
     *
     * @param array $data
     * @return string|array
     */
    public function substDistroServerVars($data)
    {
        $this->getDistroServerVars();
        return str_replace(
            array_keys($this->_substServerVars),
            array_values($this->_substServerVars),
            $data
        );
    }

    /**
     * Get module config node
     *
     * @param string $moduleName
     * @return Varien_Simplexml_Element
     */
    public function getModuleConfig($moduleName = '')
    {
        $modules = $this->getNode('modules');
        if ('' === $moduleName) {
            return $modules;
        } else {
            return $modules->$moduleName;
        }
    }

    /**
     * Get module setup class instance.
     *
     * Defaults to Mage_Core_Setup
     *
     * @param string|Varien_Simplexml_Element $module
     * @return object
     */
    public function getModuleSetup($module = '')
    {
        $className = 'Mage_Core_Setup';
        if ('' !== $module) {
            if (is_string($module)) {
                $module = $this->getModuleConfig($module);
            }
            if (isset($module->setup)) {
                $moduleClassName = $module->setup->getClassName();
                if (!empty($moduleClassName)) {
                    $className = $moduleClassName;
                }
            }
        }
        return new $className($module);
    }

    /**
     * Get temporary data directory name
     *
     * @param   string $path
     * @param   string $type
     * @return  string
     */
    public function getVarDir($path = null, $type = 'var')
    {
        $dir = Mage::getBaseDir($type) . ($path !== null ? DS . $path : '');
        if (!$this->createDirIfNotExists($dir)) {
            return false;
        }
        return $dir;
    }

    /**
     * Create dir if not exists
     *
     * @param string $dir
     * @return bool
     */
    public function createDirIfNotExists($dir)
    {
        return $this->getOptions()->createDirIfNotExists($dir);
    }

    /**
     * Get module directory by directory type
     *
     * @param   string $type
     * @param   string $moduleName
     * @return  string
     */
    public function getModuleDir($type, $moduleName)
    {
        if (isset($this->_moduleDirs[$moduleName][$type])) {
            return $this->_moduleDirs[$moduleName][$type];
        }

        $codePool = (string)$this->getModuleConfig($moduleName)->codePool;
        $dir = $this->getOptions()->getCodeDir() . DS . $codePool . DS . uc_words($moduleName, DS);

        switch ($type) {
            case 'etc':
            case 'controllers':
            case 'sql':
            case 'data':
            case 'locale':
            case 'view':
                $dir .= DS . $type;
                break;
        }

        $dir = str_replace('/', DS, $dir);
        return $dir;
    }

    /**
     * Set path to the corresponding module directory
     *
     * @param string $moduleName
     * @param string $type directory type (etc, controllers, locale etc)
     * @param string $path
     * @return Mage_Core_Model_Config
     */
    public function setModuleDir($moduleName, $type, $path)
    {
        if (!isset($this->_moduleDirs[$moduleName])) {
            $this->_moduleDirs[$moduleName] = array();
        }
        $this->_moduleDirs[$moduleName][$type] = $path;
        return $this;
    }

    /**
     * Load event observers for an area (front, admin)
     *
     * @param   string $area
     * @return  boolean
     */
    public function loadEventObservers($area)
    {
        $events = $this->getNode("$area/events");
        if ($events) {
            $events = $events->children();
        } else {
            return false;
        }

        foreach ($events as $event) {
            $eventName = $event->getName();
            $observers = $event->observers->children();
            foreach ($observers as $observer) {
                switch ((string)$observer->type) {
                    case 'singleton':
                        $callback = array(
                            Mage::getSingleton((string)$observer->class),
                            (string)$observer->method
                        );
                        break;
                    case 'object':
                    case 'model':
                        $callback = array(
                            Mage::getModel((string)$observer->class),
                            (string)$observer->method
                        );
                        break;
                    default:
                        $callback = array($observer->getClassName(), (string)$observer->method);
                        break;
                }

                $args = (array)$observer->args;
                // @codingStandardsIgnoreStart
                $observerClass = $observer->observer_class ? (string)$observer->observer_class : '';
                // @codingStandardsIgnoreEnd
                Mage::addObserver($eventName, $callback, $args, $observer->getName(), $observerClass);
            }
        }
        return true;
    }

    /**
     * Get standard path variables.
     *
     * To be used in blocks, templates, etc.
     *
     * @return array
     */
    public function getPathVars()
    {
        $path = array();

        $path['baseUrl'] = Mage::getBaseUrl();
        $path['baseSecureUrl'] = Mage::getBaseUrl('link', true);

        return $path;
    }

    /**
     * Check rewrite section and apply rewrites to $className, if any
     *
     * @param   string $className
     * @return  string
     */
    protected function _applyClassRewrites($className)
    {
        if (!isset($this->_classNameCache[$className])) {
            if (isset($this->_xml->global->rewrites->$className)) {
                $className = (string) $this->_xml->global->rewrites->$className;
            }
            $this->_classNameCache[$className] = $className;
        }

        return $this->_classNameCache[$className];
    }

    /**
     * Retrieve block class name
     *
     * @param   string $blockClass
     * @return  string
     */
    public function getBlockClassName($blockClass)
    {
        return $this->getModelClassName($blockClass);
    }

    /**
     * Retrieve helper class name
     *
     * @param   string $helperClass
     * @return  string
     */
    public function getHelperClassName($helperClass)
    {
        return $this->getModelClassName($helperClass);
    }


    /**
     * Retrieve module class name
     *
     * @param   string $modelClass
     * @return  string
     */
    public function getModelClassName($modelClass)
    {
        return $this->_applyClassRewrites($modelClass);
    }

    /**
     * Get model class instance.
     *
     * Example:
     * $config->getModelInstance('catalog/product')
     *
     * Will instantiate Mage_Catalog_Model_Resource_Product
     *
     * @param string $modelClass
     * @param array|object $constructArguments
     * @return Mage_Core_Model_Abstract|bool
     */
    public function getModelInstance($modelClass = '', $constructArguments = array())
    {
        $className = $this->getModelClassName($modelClass);
        if (class_exists($className)) {
            Magento_Profiler::start('FACTORY:' . $className, array('group' => 'FACTORY', 'class_name' => $className));
            $obj = $this->_objectManager->create($className, $constructArguments);
            Magento_Profiler::stop('FACTORY:' . $className);
            return $obj;
        } else {
            return false;
        }
    }

    /**
     * Get resource model object by alias
     *
     * @param   string $modelClass
     * @param   array $constructArguments
     * @return  object
     */
    public function getResourceModelInstance($modelClass = '', $constructArguments = array())
    {
        return $this->getModelInstance($modelClass, $constructArguments);
    }

    /**
     * Get resource configuration for resource name
     *
     * @param string $name
     * @return Varien_Simplexml_Element
     */
    public function getResourceConfig($name)
    {
        return $this->_xml->global->resources->{$name};
    }

    /**
     * Get connection configuration
     *
     * @param   string $name
     * @return  Varien_Simplexml_Element
     */
    public function getResourceConnectionConfig($name)
    {
        $config = $this->getResourceConfig($name);
        if ($config) {
            $conn = $config->connection;
            if ($conn) {
                if (!empty($conn->use)) {
                    return $this->getResourceConnectionConfig((string)$conn->use);
                } else {
                    return $conn;
                }
            }
        }
        return false;
    }

    /**
     * Retrieve resource type configuration for resource name
     *
     * @param string $type
     * @return Varien_Simplexml_Element
     */
    public function getResourceTypeConfig($type)
    {
        return $this->_xml->global->resource->connection->types->{$type};
    }

    /**
     * Retrieve store Ids for $path with checking
     *
     * if empty $allowValues then retrieve all stores values
     *
     * return array($storeId => $pathValue)
     *
     * @param   string $path
     * @param   array  $allowValues
     * @param   string $useAsKey
     * @return  array
     */
    public function getStoresConfigByPath($path, $allowValues = array(), $useAsKey = 'id')
    {
        $storeValues = array();
        $stores = $this->getNode('stores');
        foreach ($stores->children() as $code => $store) {
            switch ($useAsKey) {
                case 'id':
                    $key = (int) $store->descend('system/store/id');
                    break;
                case 'code':
                    $key = $code;
                    break;
                case 'name':
                    $key = (string) $store->descend('system/store/name');
                    break;
                default:
                    break;
            }
            if ($key === false) {
                continue;
            }

            $pathValue = (string) $store->descend($path);

            if (empty($allowValues)) {
                $storeValues[$key] = $pathValue;
            } else if (in_array($pathValue, $allowValues)) {
                $storeValues[$key] = $pathValue;
            }
        }
        return $storeValues;
    }

    /**
     * Check whether given path should be secure according to configuration security requirements for URL
     * "Secure" should not be confused with https protocol, it is about web/secure/*_url settings usage only
     *
     * @param string $url
     * @return bool
     */
    public function shouldUrlBeSecure($url)
    {
        if (!Mage::getStoreConfigFlag(Mage_Core_Model_Store::XML_PATH_SECURE_IN_FRONTEND)) {
            return false;
        }

        if (!isset($this->_secureUrlCache[$url])) {
            $this->_secureUrlCache[$url] = false;
            $secureUrls = $this->getNode('frontend/secure_url');
            foreach ($secureUrls->children() as $match) {
                if (strpos($url, (string)$match) === 0) {
                    $this->_secureUrlCache[$url] = true;
                    break;
                }
            }
        }

        return $this->_secureUrlCache[$url];
    }

    /**
     * Get DB table names prefix
     *
     * @return string
     */
    public function getTablePrefix()
    {
        return $this->_xml->global->resources->db->table_prefix;
    }

    /**
     * Get events configuration
     *
     * @param   string $area event area
     * @param   string $eventName event name
     * @return  Mage_Core_Model_Config_Element
     */
    public function getEventConfig($area, $eventName)
    {
        if (!isset($this->_eventAreas[$area])) {
            $this->_eventAreas[$area] = $this->getNode($area)->events;
        }
        return $this->_eventAreas[$area]->{$eventName};
    }

    /**
     * Save config value to DB
     *
     * @param string $path
     * @param string $value
     * @param string $scope
     * @param int $scopeId
     * @return Mage_Core_Model_Store_Config
     */
    public function saveConfig($path, $value, $scope = Mage_Core_Model_Store::DEFAULT_CODE, $scopeId = 0)
    {
        $resource = $this->getResourceModel();
        $resource->saveConfig(rtrim($path, '/'), $value, $scope, $scopeId);

        return $this;
    }

    /**
     * Delete config value from DB
     *
     * @param   string $path
     * @param   string $scope
     * @param   int $scopeId
     * @return  Mage_Core_Model_Config
     */
    public function deleteConfig($path, $scope = Mage_Core_Model_Store::DEFAULT_CODE, $scopeId = 0)
    {
        $resource = $this->getResourceModel();
        $resource->deleteConfig(rtrim($path, '/'), $scope, $scopeId);

        return $this;
    }

    /**
     * Get fieldset from configuration
     *
     * @param string $name fieldset name
     * @param string $root fieldset area, could be 'admin'
     * @return null|array
     */
    public function getFieldset($name, $root = 'global')
    {
        /** @var $config Mage_Core_Model_Config_Base */
        $config = Mage::getSingleton('Mage_Core_Model_Config_Fieldset');
        $rootNode = $config->getNode($root . '/fieldsets');
        if (!$rootNode) {
            return null;
        }
        return $rootNode->$name ? $rootNode->$name->children() : null;
    }

    /**
     * Retrieve resource connection model name
     *
     * @param string $moduleName
     * @return string
     */
    public function getResourceConnectionModel($moduleName = null)
    {
        $config = null;
        if (!is_null($moduleName)) {
            $setupResource = $moduleName . '_setup';
            $config        = $this->getResourceConnectionConfig($setupResource);
        }
        if (!$config) {
            $config = $this->getResourceConnectionConfig(Mage_Core_Model_Resource::DEFAULT_SETUP_RESOURCE);
        }

        return (string)$config->model;
    }

    /**
     * Get a resource model class name
     *
     * @param string $modelClass
     * @return string|bool
     */
    public function getResourceModelClassName($modelClass)
    {
        return $this->getModelClassName($modelClass);
    }

    /**
     *  Get allowed areas
     *
     * @return array
     */
    public function getAreas()
    {
        if (is_null($this->_allowedAreas) ) {
            $this->_loadAreas();
        }

        return $this->_allowedAreas;
    }

    /**
     * Retrieve area config by area code
     *
     * @param string|null $areaCode
     * @return array
     * @throws InvalidArgumentException
     */
    public function getAreaConfig($areaCode = null)
    {
        $areaCode = empty($areaCode) ? $this->getCurrentAreaCode() : $areaCode;
        $areas = $this->getAreas();
        if (!isset($areas[$areaCode])) {
            throw new InvalidArgumentException('Requested area (' . $areaCode . ') doesn\'t exist');
        }
        return $areas[$areaCode];
    }

    /**
     * Load allowed areas from config
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return Mage_Core_Model_Config
     */
    protected function _loadAreas()
    {
        $this->_allowedAreas = array();
        $nodeAreas = $this->getNode('global/areas');
        if (is_object($nodeAreas)) {
            foreach ($nodeAreas->asArray() as $areaCode => $areaInfo) {
                if (empty($areaCode)
                    || (!isset($areaInfo['base_controller']) || empty($areaInfo['base_controller']))
                ) {
                    continue;
                }
                /**
                 * TODO: Check of 'routers' nodes existance is excessive:
                 * TODO: 'routers' check is moved Mage_Core_Model_Config::getRouters()
                 */

                /**
                 * TODO: Routers are not required in API.
                 * TODO: That is why Check for empty router class moved to Mage_Core_Model_Config::getRouters()
                 */
                $this->_allowedAreas[$areaCode] = $areaInfo;
            }
        }

        return $this;
    }

    /**
     * Get routers from config
     *
     * @return array
     */
    public function getRouters()
    {
        $routers = array();
        foreach ($this->getAreas() as $areaCode => $areaInfo) {
            if (isset($areaInfo['routers']) && is_array($areaInfo['routers'])) {
                foreach ($areaInfo['routers'] as $routerKey => $routerInfo ) {
                    if (!isset($routerInfo['class']) || empty($routerInfo['class'])) {
                        continue;
                    }
                    $routerInfo = array_merge($routerInfo, $areaInfo);
                    unset($routerInfo['routers']);
                    $routerInfo['area'] = $areaCode;
                    $routers[$routerKey] = $routerInfo;
                }
            }
        }
        return $routers;
    }

    /**
     * Is module enabled
     *
     * @param string $moduleName
     * @return bool
     */
    public function isModuleEnabled($moduleName)
    {
        if (!$this->getNode('modules/' . $moduleName)) {
            return false;
        }

        $isActive = $this->getNode('modules/' . $moduleName . '/active');
        if (!$isActive || !in_array((string)$isActive, array('true', '1'))) {
            return false;
        }
        return true;
    }

    /**
     * Get currently used area code
     * @return string|null
     */
    public function getCurrentAreaCode()
    {
        return $this->_currentAreaCode;
    }

    /**
     * Set currently used area code
     *
     * @param $areaCode
     * @return Mage_Core_Model_Config
     */
    public function setCurrentAreaCode($areaCode)
    {
        $this->_currentAreaCode = $areaCode;
        return $this;
    }

    /**
     * Cleanup circular references
     *
     * Destructor should be called explicitly in order to work around the PHP bug
     * https://bugs.php.net/bug.php?id=62468
     */
    public function __destruct()
    {
        $this->_cacheLoadedSections = array();
        $this->_prototype = null;
        parent::__destruct();
    }
}
