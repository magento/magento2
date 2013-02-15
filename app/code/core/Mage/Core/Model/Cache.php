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
 * System cache model
 * support id and tags preffix support,
 */

class Mage_Core_Model_Cache implements Mage_Core_Model_CacheInterface
{
    const DEFAULT_LIFETIME  = 7200;
    const OPTIONS_CACHE_ID  = 'core_cache_options';
    const INVALIDATED_TYPES = 'core_cache_invalidate';
    const XML_PATH_TYPES    = 'global/cache/types';

    /**
     * Inject custom cache settings in application initialization
     */
    const APP_INIT_PARAM = 'cache';

    /**
     * @var Mage_Core_Model_Config
     */
    protected $_config;

    /**
     * @var Mage_Core_Model_Factory_Helper
     */
    protected $_helperFactory;

    /**
     * @var string
     */
    protected $_idPrefix    = '';

    /**
     * Cache frontend API
     *
     * @var Zend_Cache_Core
     */
    protected $_frontend    = null;

    /**
     * Shared memory backend models list (required TwoLevels backend model)
     *
     * @var array
     */
    protected $_shmBackends = array(
        'apc', 'memcached', 'xcache',
        'zendserver_shmem', 'zendserver_disk', 'varien_eaccelerator',
    );

    /**
     * Fefault cache backend type
     *
     * @var string
     */
    protected $_defaultBackend = 'File';

    /**
     * Default iotions for default backend
     *
     * @var array
     */
    protected $_defaultBackendOptions = array(
        'hashed_directory_level'    => 1,
        'hashed_directory_umask'    => 0777,
        'file_name_prefix'          => 'mage',
    );

    /**
     * List of available request processors
     *
     * @var array
     */
    protected $_requestProcessors = array();

    /**
     * Disallow cache saving
     *
     * @var bool
     */
    protected $_disallowSave = false;

    /**
     * List of allowed cache options
     *
     * @var array
     */
    protected $_allowedCacheOptions = null;

    /**
     * @var bool
     */
    protected $_globalBanUseCache = false;

    /**
     * @param Mage_Core_Model_Config $config
     * @param Mage_Core_Model_Config_Primary $cacheConfig
     * @param Mage_Core_Model_Dir $dirs
     * @param Mage_Core_Model_Factory_Helper $helperFactory
     * @param bool $banCache
     * @param array $options
     */
    public function __construct(
        Mage_Core_Model_ConfigInterface $config,
        Mage_Core_Model_Config_Primary $cacheConfig,
        Mage_Core_Model_Dir $dirs,
        Mage_Core_Model_Factory_Helper $helperFactory,
        $banCache = false,
        array $options = array()
    ) {
        $configOptions = $cacheConfig->getNode('global/cache');
        if ($configOptions) {
            $configOptions = $configOptions->asArray();
        } else {
            $configOptions = array();
        }
        $options = array_merge($configOptions, $options);

        $this->_config = $config;
        $this->_helperFactory = $helperFactory;
        $this->_globalBanUseCache = $banCache;

        $this->_defaultBackendOptions['cache_dir'] = $dirs->getDir(Mage_Core_Model_Dir::CACHE);
        /**
         * Initialize id prefix
         */
        $this->_idPrefix = isset($options['id_prefix']) ? $options['id_prefix'] : '';
        if (!$this->_idPrefix && isset($options['prefix'])) {
            $this->_idPrefix = $options['prefix'];
        }
        if (empty($this->_idPrefix)) {
            $this->_idPrefix = substr(md5($dirs->getDir(Mage_Core_Model_Dir::CONFIG)), 0, 3) . '_';
        }

        $backend = $this->_getBackendOptions($options);
        $frontend = $this->_getFrontendOptions($options);

        // Start profiling
        $profilerTags = $this->_generateProfilerTags('create', $backend['type'], $frontend['type']);

        Magento_Profiler::start('cache_frontend_create', $profilerTags);

        // create cache
        $this->_frontend = Zend_Cache::factory($frontend['type'], $backend['type'], $frontend, $backend['options'],
            true, true, true
        );

        // stop profiling
        Magento_Profiler::stop('cache_frontend_create');

        if (isset($options['request_processors'])) {
            $this->_requestProcessors = $options['request_processors'];
        }

        if (isset($options['disallow_save'])) {
            $this->_disallowSave = $options['disallow_save'];
        }
    }

    /**
     * Get cache backend options. Result array contain backend type ('type' key) and backend options ('options')
     *
     * @param   array $cacheOptions
     * @return  array
     */
    protected function _getBackendOptions(array $cacheOptions)
    {
        $enable2levels = false;
        $type   = isset($cacheOptions['backend']) ? $cacheOptions['backend'] : $this->_defaultBackend;
        if (isset($cacheOptions['backend_options']) && is_array($cacheOptions['backend_options'])) {
            $options = $cacheOptions['backend_options'];
        } else {
            $options = array();
        }

        $backendType = false;
        switch (strtolower($type)) {
            case 'sqlite':
                if (extension_loaded('sqlite') && isset($options['cache_db_complete_path'])) {
                    $backendType = 'Sqlite';
                }
                break;
            case 'memcached':
                if (extension_loaded('memcached')) {
                    if (isset($cacheOptions['memcached'])) {
                        $options = $cacheOptions['memcached'];
                    }
                    $enable2levels = true;
                    $backendType = 'Libmemcached';
                } elseif (extension_loaded('memcache')) {
                    if (isset($cacheOptions['memcached'])) {
                        $options = $cacheOptions['memcached'];
                    }
                    $enable2levels = true;
                    $backendType = 'Memcached';
                }
                break;
            case 'apc':
                if (extension_loaded('apc') && ini_get('apc.enabled')) {
                    $enable2levels = true;
                    $backendType = 'Apc';
                }
                break;
            case 'xcache':
                if (extension_loaded('xcache')) {
                    $enable2levels = true;
                    $backendType = 'Xcache';
                }
                break;
            case 'eaccelerator':
            case 'varien_cache_backend_eaccelerator':
                if (extension_loaded('eaccelerator') && ini_get('eaccelerator.enable')) {
                    $enable2levels = true;
                    $backendType = 'Varien_Cache_Backend_Eaccelerator';
                }
                break;
            case 'database':
                $backendType = 'Varien_Cache_Backend_Database';
                $options = $this->getDbAdapterOptions();
                break;
            default:
                if ($type != $this->_defaultBackend) {
                    try {
                        if (class_exists($type, true)) {
                            $implements = class_implements($type, true);
                            if (in_array('Zend_Cache_Backend_Interface', $implements)) {
                                $backendType = $type;
                            }
                        }
                    } catch (Exception $e) {
                    }
                }
        }

        if (!$backendType) {
            $backendType = $this->_defaultBackend;
            foreach ($this->_defaultBackendOptions as $option => $value) {
                if (!array_key_exists($option, $options)) {
                    $options[$option] = $value;
                }
            }
        }

        $backendOptions = array('type' => $backendType, 'options' => $options);
        if ($enable2levels) {
            $backendOptions = $this->_getTwoLevelsBackendOptions($backendOptions, $cacheOptions);
        }
        return $backendOptions;
    }

    /**
     * Get options for database backend type
     *
     * @return array
     */
    protected function getDbAdapterOptions()
    {
        $options['adapter_callback'] = array($this, 'getDbAdapter');
        $options['data_table']  = Mage::getSingleton('Mage_Core_Model_Resource')->getTableName('core_cache');
        $options['tags_table']  = Mage::getSingleton('Mage_Core_Model_Resource')->getTableName('core_cache_tag');
        return $options;
    }

    /**
     * Initialize two levels backend model options
     *
     * @param array $fastOptions fast level backend type and options
     * @param array $cacheOptions all cache options
     * @return array
     */
    protected function _getTwoLevelsBackendOptions($fastOptions, $cacheOptions)
    {
        $options = array();
        $options['fast_backend']                = $fastOptions['type'];
        $options['fast_backend_options']        = $fastOptions['options'];
        $options['fast_backend_custom_naming']  = true;
        $options['fast_backend_autoload']       = true;
        $options['slow_backend_custom_naming']  = true;
        $options['slow_backend_autoload']       = true;

        if (isset($cacheOptions['auto_refresh_fast_cache'])) {
            $options['auto_refresh_fast_cache'] = (bool)$cacheOptions['auto_refresh_fast_cache'];
        } else {
            $options['auto_refresh_fast_cache'] = false;
        }
        if (isset($cacheOptions['slow_backend'])) {
            $options['slow_backend'] = $cacheOptions['slow_backend'];
        } else {
            $options['slow_backend'] = $this->_defaultBackend;
        }
        if (isset($cacheOptions['slow_backend_options'])) {
            $options['slow_backend_options'] = $cacheOptions['slow_backend_options'];
        } else {
            $options['slow_backend_options'] = $this->_defaultBackendOptions;
        }
        if ($options['slow_backend'] == 'database') {
            $options['slow_backend'] = 'Varien_Cache_Backend_Database';
            $options['slow_backend_options'] = $this->getDbAdapterOptions();
            if (isset($cacheOptions['slow_backend_store_data'])) {
                $options['slow_backend_options']['store_data'] = (bool)$cacheOptions['slow_backend_store_data'];
            } else {
                $options['slow_backend_options']['store_data'] = false;
            }
        }

        $backend = array(
            'type'      => 'TwoLevels',
            'options'   => $options
        );
        return $backend;
    }

    /**
     * Get options of cache frontend (options of Zend_Cache_Core)
     *
     * @param   array $cacheOptions
     * @return  array
     */
    protected function _getFrontendOptions(array $cacheOptions)
    {
        $options = isset($cacheOptions['frontend_options']) ? $cacheOptions['frontend_options'] : array();
        if (!array_key_exists('caching', $options)) {
            $options['caching'] = true;
        }
        if (!array_key_exists('lifetime', $options)) {
            $options['lifetime'] = isset($cacheOptions['lifetime']) ? $cacheOptions['lifetime']
                : self::DEFAULT_LIFETIME;
        }
        if (!array_key_exists('automatic_cleaning_factor', $options)) {
            $options['automatic_cleaning_factor'] = 0;
        }
        $options['cache_id_prefix'] = $this->_idPrefix;
        $options['type'] = isset($cacheOptions['frontend']) ? $cacheOptions['frontend'] : 'Varien_Cache_Core';
        return $options;
    }

    /**
     * Prepare unified valid identifier with preffix
     *
     * @param   string $id
     * @return  string
     */
    protected function _id($id)
    {
        if ($id) {
            $id = strtoupper($id);
        }
        return $id;
    }

    /**
     * Prepare cache tags.
     *
     * @param   array $tags
     * @return  array
     */
    protected function _tags($tags = array())
    {
        foreach ($tags as $key => $value) {
            $tags[$key] = $this->_id($value);
        }
        return $tags;
    }

    /**
     * Generate Magento Profiler tags
     *
     * @param string $operation
     * @param string $frontendType
     * @param string $backendType
     * @return array
     */
    protected function _generateProfilerTags($operation, $frontendType = '', $backendType = '')
    {
        $profilerTags = array('group' => 'cache',
            'operation' => 'cache:' . $operation);

        if (!empty($frontendType)) {
            $profilerTags['frontend_type'] = $frontendType;
        } elseif ($this->_frontend) {
            $profilerTags['frontend_type'] = get_class($this->_frontend);
        }

        if (!empty($backendType)) {
            $profilerTags['backend_type'] = $backendType;
        } elseif ($this->_frontend) {
            $parsedBackendType = $this->_getBackendType();
            if ($parsedBackendType) {
                $profilerTags['backend_type'] = $parsedBackendType;
            }
        }

        return $profilerTags;
    }

    /**
     * Get cache backend type
     *
     * @return string
     */
    protected function _getBackendType()
    {
        $backendType = '';

        if ($this->_frontend) {
            $backend = $this->_frontend->getBackend();
            $backendClass = get_class($backend);

            $possibleCacheBackends = array('Zend_Cache_Backend_', 'Varien_Cache_Backend_');
            foreach ($possibleCacheBackends as $backendClassStart) {
                if (substr($backendClass, 0, strlen($backendClassStart)) == $backendClassStart) {
                    $backendType = substr($backendClass, strlen($backendClassStart));
                    break;
                }
            }
        }

        return $backendType;
    }

    /**
     * Get cache frontend API object
     *
     * @return Zend_Cache_Core
     */
    public function getFrontend()
    {
        return $this->_frontend;
    }

    /**
     * Load data from cache by id
     *
     * @param   string $id
     * @return  string
     */
    public function load($id)
    {
        Magento_Profiler::start('cache_load', $this->_generateProfilerTags('load'));
        $result = $this->_frontend->load($this->_id($id));
        Magento_Profiler::stop('cache_load');

        return $result;
    }

    /**
     * Save data
     *
     * @param string $data
     * @param string $id
     * @param array $tags
     * @param int $lifeTime
     * @return bool
     */
    public function save($data, $id, $tags=array(), $lifeTime=null)
    {
        /**
         * Add global magento cache tag to all cached data exclude config cache
         */
        if (!in_array(Mage_Core_Model_Config::CACHE_TAG, $tags)) {
            $tags[] = Mage_Core_Model_AppInterface::CACHE_TAG;
        }
        if ($this->_disallowSave) {
            return true;
        }

        Magento_Profiler::start('cache_save', $this->_generateProfilerTags('save'));
        $result = $this->_frontend->save((string)$data, $this->_id($id), $this->_tags($tags), $lifeTime);
        Magento_Profiler::stop('cache_save');

        return $result;
    }

    /**
     * Remove cached data by identifier
     *
     * @param string $id
     * @return bool
     */
    public function remove($id)
    {
        Magento_Profiler::start('cache_remove', $this->_generateProfilerTags('remove'));
        $result = $this->_frontend->remove($this->_id($id));
        Magento_Profiler::stop('cache_remove');

        return $result;
    }

    /**
     * Clean cached data by specific tag
     *
     * @param array $tags
     * @return bool
     */
    public function clean($tags = array())
    {
        Magento_Profiler::start('cache_clean', $this->_generateProfilerTags('clean'));

        $mode = Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG;
        if (!empty($tags)) {
            if (!is_array($tags)) {
                $tags = array($tags);
            }
            $res = $this->_frontend->clean($mode, $this->_tags($tags));
        } else {
            $res = $this->_frontend->clean($mode, array(Mage_Core_Model_AppInterface::CACHE_TAG));
            $res = $res && $this->_frontend->clean($mode, array(Mage_Core_Model_Config::CACHE_TAG));
        }

        Magento_Profiler::stop('cache_clean');

        return $res;
    }

    /**
     * Clean cached data by specific tag
     *
     * @return bool
     */
    public function flush()
    {
        Magento_Profiler::start('cache_flush', $this->_generateProfilerTags('flush'));
        $res = $this->_frontend->clean();
        Magento_Profiler::stop('cache_flush');

        return $res;
    }

    /**
     * Get adapter for database cache backend model
     *
     * @return Zend_Db_Adapter_Abstract
     */
    public function getDbAdapter()
    {
        return Mage::getSingleton('Mage_Core_Model_Resource')->getConnection('core_write');
    }

    /**
     * Get cache resource model
     *
     * @return Mage_Core_Model_Resource_Cache
     */
    protected function _getResource()
    {
        return Mage::getResourceSingleton('Mage_Core_Model_Resource_Cache');
    }

    /**
     * Initialize cache types options
     *
     * @return Mage_Core_Model_Cache
     */
    protected function _initOptions()
    {
        $options = $this->load(self::OPTIONS_CACHE_ID);
        if ($options === false) {
            $options = $this->_getResource()->getAllOptions();
            if (is_array($options)) {
                $this->_allowedCacheOptions = $options;
                $this->save(serialize($this->_allowedCacheOptions), self::OPTIONS_CACHE_ID);
            } else {
                $this->_allowedCacheOptions = array();
            }
        } else {
            $this->_allowedCacheOptions = unserialize($options);
        }

        if ($this->_globalBanUseCache) {
            foreach ($this->_allowedCacheOptions as $key => $val) {
                $this->_allowedCacheOptions[$key] = false;
            }
        }

        return $this;
    }

    /**
     * Save cache usage options
     *
     * @param array $options
     * @return Mage_Core_Model_Cache
     */
    public function saveOptions($options)
    {
        $this->remove(self::OPTIONS_CACHE_ID);
        $options = $this->_getResource()->saveAllOptions($options);
        return $this;
    }

    /**
     * Check if cache can be used for specific data type
     *
     * @param string $typeCode
     * @return bool
     */
    public function canUse($typeCode)
    {
        if (is_null($this->_allowedCacheOptions)) {
            $this->_initOptions();
        }

        if (empty($typeCode)) {
            return $this->_allowedCacheOptions;
        } else {
            if (isset($this->_allowedCacheOptions[$typeCode])) {
                return (bool)$this->_allowedCacheOptions[$typeCode];
            } else {
                return false;
            }
        }
    }

    /**
     * Disable cache usage for specific data type
     *
     * @param string $typeCode
     * @return Mage_Core_Model_Cache
     */
    public function banUse($typeCode)
    {
        $this->_allowedCacheOptions[$typeCode] = false;
        return $this;
    }

    /**
     * Enable cache usage for specific data type
     *
     * @param string $typeCode
     * @return Mage_Core_Model_Cache
     */
    public function allowUse($typeCode)
    {
        $this->_allowedCacheOptions[$typeCode] = true;
        return $this;
    }

    /**
     * Get cache tags by cache type from configuration
     *
     * @param string $type
     * @return array
     */
    public function getTagsByType($type)
    {
        $path = self::XML_PATH_TYPES.'/'.$type.'/tags';
        $tagsConfig = $this->_config->getNode($path);
        if ($tagsConfig) {
            $tags = (string) $tagsConfig;
            $tags = explode(',', $tags);
        } else {
            $tags = false;
        }
        return $tags;
    }

    /**
     * Get information about all declared cache types
     *
     * @return array
     */
    public function getTypes()
    {
        $types = array();
        $config = $this->_config->getNode(self::XML_PATH_TYPES);
        if ($config) {
            /** @var $helper Mage_Core_Helper_Data*/
            $helper = $this->_helperFactory->get('Mage_Core_Helper_Data');
            foreach ($config->children() as $type=>$node) {
                $types[$type] = new Varien_Object(array(
                    'id'            => $type,
                    'cache_type'    => $helper->__((string)$node->label),
                    'description'   => $helper->__((string)$node->description),
                    'tags'          => strtoupper((string) $node->tags),
                    'status'        => (int)$this->canUse($type),
                ));
            }
        }
        return $types;
    }

    /**
     * Get invalidate types codes
     *
     * @return array
     */
    protected function _getInvalidatedTypes()
    {
        $types = $this->load(self::INVALIDATED_TYPES);
        if ($types) {
            $types = unserialize($types);
        } else {
            $types = array();
        }
        return $types;
    }

    /**
     * Save invalidated cache types
     *
     * @param array $types
     * @return Mage_Core_Model_Cache
     */
    protected function _saveInvalidatedTypes($types)
    {
        $this->save(serialize($types), self::INVALIDATED_TYPES);
        return $this;
    }

    /**
     * Get array of all invalidated cache types
     *
     * @return array
     */
    public function getInvalidatedTypes()
    {
        $invalidatedTypes = array();
        $types = $this->_getInvalidatedTypes();
        if ($types) {
            $allTypes = $this->getTypes();
            foreach ($types as $type => $flag) {
                if (isset($allTypes[$type]) && $this->canUse($type)) {
                    $invalidatedTypes[$type] = $allTypes[$type];
                }
            }
        }
        return $invalidatedTypes;
    }

    /**
     * Mark specific cache type(s) as invalidated
     *
     * @param string|array $typeCode
     * @return Mage_Core_Model_Cache
     */
    public function invalidateType($typeCode)
    {
        $types = $this->_getInvalidatedTypes();
        if (!is_array($typeCode)) {
            $typeCode = array($typeCode);
        }
        foreach ($typeCode as $code) {
            $types[$code] = 1;
        }
        $this->_saveInvalidatedTypes($types);
        return $this;
    }

    /**
     * Clean cached data for specific cache type
     *
     * @param string $typeCode
     * @return Mage_Core_Model_Cache
     */
    public function cleanType($typeCode)
    {
        $tags = $this->getTagsByType($typeCode);
        $this->clean($tags);

        $types = $this->_getInvalidatedTypes();
        unset($types[$typeCode]);
        $this->_saveInvalidatedTypes($types);
        return $this;
    }
}
