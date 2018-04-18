<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Factory that creates cache frontend instances based on options
 */
namespace Magento\Framework\App\Cache\Frontend;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DriverInterface;

class Factory
{
    /**
     * Default cache entry lifetime
     */
    const DEFAULT_LIFETIME = 7200;

    /**
     * Caching params, that applied for all cache frontends regardless of type
     */
    const PARAM_CACHE_FORCED_OPTIONS = 'cache_options';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * @var Filesystem
     */
    private $_filesystem;

    /**
     * Cache options to be enforced for all instances being created
     *
     * @var array
     */
    private $_enforcedOptions = [];

    /**
     * Configuration of decorators that are to be applied to every cache frontend being instantiated, format:
     * array(
     *  array('class' => '<decorator_class>', 'arguments' => array()),
     *  ...
     * )
     *
     * @var array
     */
    private $_decorators = [];

    /**
     * Default cache backend type
     *
     * @var string
     */
    protected $_defaultBackend = 'Cm_Cache_Backend_File';

    /**
     * Options for default backend
     *
     * @var array
     */
    protected $_backendOptions = [
        'hashed_directory_level' => 1,
        'file_name_prefix' => 'mage',
    ];

    /**
     * Resource
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param Filesystem $filesystem
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param array $enforcedOptions
     * @param array $decorators
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Filesystem $filesystem,
        \Magento\Framework\App\ResourceConnection $resource,
        array $enforcedOptions = [],
        array $decorators = []
    ) {
        $this->_objectManager = $objectManager;
        $this->_filesystem = $filesystem;
        $this->_resource = $resource;
        $this->_enforcedOptions = $enforcedOptions;
        $this->_decorators = $decorators;
    }

    /**
     * Return newly created cache frontend instance
     *
     * @param array $options
     * @return \Magento\Framework\Cache\FrontendInterface
     */
    public function create(array $options)
    {
        $options = $this->_getExpandedOptions($options);

        foreach (['backend_options', 'slow_backend_options'] as $section) {
            if (!empty($options[$section]['cache_dir'])) {
                $directory = $this->_filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
                $directory->create($options[$section]['cache_dir']);
                $options[$section]['cache_dir'] = $directory->getAbsolutePath($options[$section]['cache_dir']);
            }
        }

        $idPrefix = isset($options['id_prefix']) ? $options['id_prefix'] : '';
        if (!$idPrefix && isset($options['prefix'])) {
            $idPrefix = $options['prefix'];
        }
        if (empty($idPrefix)) {
            $configDirPath = $this->_filesystem->getDirectoryRead(DirectoryList::CONFIG)->getAbsolutePath();
            $idPrefix =
                substr(md5($configDirPath), 0, 3) . '_';
        }
        $options['frontend_options']['cache_id_prefix'] = $idPrefix;

        $backend = $this->_getBackendOptions($options);
        $frontend = $this->_getFrontendOptions($options);

        // Start profiling
        $profilerTags = [
            'group' => 'cache',
            'operation' => 'cache:create',
            'frontend_type' => $frontend['type'],
            'backend_type' => $backend['type'],
        ];
        \Magento\Framework\Profiler::start('cache_frontend_create', $profilerTags);

        /** @var $result \Magento\Framework\Cache\Frontend\Adapter\Zend */
        $result = $this->_objectManager->create(
            'Magento\Framework\Cache\Frontend\Adapter\Zend',
            [
                'frontend' => \Zend_Cache::factory(
                    $frontend['type'],
                    $backend['type'],
                    $frontend,
                    $backend['options'],
                    true,
                    true,
                    true
                )
            ]
        );
        $result = $this->_applyDecorators($result);

        // stop profiling
        \Magento\Framework\Profiler::stop('cache_frontend_create');
        return $result;
    }

    /**
     * Return options expanded with enforced values
     *
     * @param array $options
     * @return array
     */
    private function _getExpandedOptions(array $options)
    {
        return array_replace_recursive($options, $this->_enforcedOptions);
    }

    /**
     * Apply decorators to a cache frontend instance and return the topmost one
     *
     * @param \Magento\Framework\Cache\FrontendInterface $frontend
     * @return \Magento\Framework\Cache\FrontendInterface
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    private function _applyDecorators(\Magento\Framework\Cache\FrontendInterface $frontend)
    {
        foreach ($this->_decorators as $decoratorConfig) {
            if (!isset($decoratorConfig['class'])) {
                throw new \LogicException('Class has to be specified for a cache frontend decorator.');
            }
            $decoratorClass = $decoratorConfig['class'];
            $decoratorParams = isset($decoratorConfig['parameters']) ? $decoratorConfig['parameters'] : [];
            $decoratorParams['frontend'] = $frontend;
            // conventionally, 'frontend' argument is a decoration subject
            $frontend = $this->_objectManager->create($decoratorClass, $decoratorParams);
            if (!$frontend instanceof \Magento\Framework\Cache\FrontendInterface) {
                throw new \UnexpectedValueException('Decorator has to implement the cache frontend interface.');
            }
        }
        return $frontend;
    }

    /**
     * Get cache backend options. Result array contain backend type ('type' key) and backend options ('options')
     *
     * @param  array $cacheOptions
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getBackendOptions(array $cacheOptions)
    {
        $enableTwoLevels = false;
        $type = isset($cacheOptions['backend']) ? $cacheOptions['backend'] : $this->_defaultBackend;
        if (isset($cacheOptions['backend_options']) && is_array($cacheOptions['backend_options'])) {
            $options = $cacheOptions['backend_options'];
        } else {
            $options = [];
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
                    $enableTwoLevels = true;
                    $backendType = 'Libmemcached';
                } elseif (extension_loaded('memcache')) {
                    if (isset($cacheOptions['memcached'])) {
                        $options = $cacheOptions['memcached'];
                    }
                    $enableTwoLevels = true;
                    $backendType = 'Memcached';
                }
                break;
            case 'apc':
                if (extension_loaded('apc') && ini_get('apc.enabled')) {
                    $enableTwoLevels = true;
                    $backendType = 'Apc';
                }
                break;
            case 'xcache':
                if (extension_loaded('xcache')) {
                    $enableTwoLevels = true;
                    $backendType = 'Xcache';
                }
                break;
            case 'eaccelerator':
            case 'varien_cache_backend_eaccelerator':
                if (extension_loaded('eaccelerator') && ini_get('eaccelerator.enable')) {
                    $enableTwoLevels = true;
                    $backendType = 'Magento\Framework\Cache\Backend\Eaccelerator';
                }
                break;
            case 'database':
                $backendType = 'Magento\Framework\Cache\Backend\Database';
                $options = $this->_getDbAdapterOptions();
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
                    } catch (\Exception $e) {
                    }
                }
        }
        if (!$backendType) {
            $backendType = $this->_defaultBackend;
            $cacheDir = $this->_filesystem->getDirectoryWrite(DirectoryList::CACHE);
            $this->_backendOptions['cache_dir'] = $cacheDir->getAbsolutePath();
            $cacheDir->create();
        }
        foreach ($this->_backendOptions as $option => $value) {
            if (!array_key_exists($option, $options)) {
                $options[$option] = $value;
            }
        }

        $backendOptions = ['type' => $backendType, 'options' => $options];
        if ($enableTwoLevels) {
            $backendOptions = $this->_getTwoLevelsBackendOptions($backendOptions, $cacheOptions);
        }
        return $backendOptions;
    }

    /**
     * Get options for database backend type
     *
     * @return array
     */
    protected function _getDbAdapterOptions()
    {
        $options['adapter_callback'] = function () {
            return $this->_resource->getConnection();
        };
        $options['data_table_callback'] = function () {
            return $this->_resource->getTableName('cache');
        };
        $options['tags_table_callback'] = function () {
            return $this->_resource->getTableName('cache_tag');
        };
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
        $options = [];
        $options['fast_backend'] = $fastOptions['type'];
        $options['fast_backend_options'] = $fastOptions['options'];
        $options['fast_backend_custom_naming'] = true;
        $options['fast_backend_autoload'] = true;
        $options['slow_backend_custom_naming'] = true;
        $options['slow_backend_autoload'] = true;

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
            $options['slow_backend_options'] = $this->_backendOptions;
        }
        if ($options['slow_backend'] == 'database') {
            $options['slow_backend'] = 'Magento\Framework\Cache\Backend\Database';
            $options['slow_backend_options'] = $this->_getDbAdapterOptions();
            if (isset($cacheOptions['slow_backend_store_data'])) {
                $options['slow_backend_options']['store_data'] = (bool)$cacheOptions['slow_backend_store_data'];
            } else {
                $options['slow_backend_options']['store_data'] = false;
            }
        }

        $backend = ['type' => 'TwoLevels', 'options' => $options];
        return $backend;
    }

    /**
     * Get options of cache frontend (options of \Zend_Cache_Core)
     *
     * @param  array $cacheOptions
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getFrontendOptions(array $cacheOptions)
    {
        $options = isset($cacheOptions['frontend_options']) ? $cacheOptions['frontend_options'] : [];
        if (!array_key_exists('caching', $options)) {
            $options['caching'] = true;
        }
        if (!array_key_exists('lifetime', $options)) {
            $options['lifetime'] = isset(
                $cacheOptions['lifetime']
            ) ? $cacheOptions['lifetime'] : self::DEFAULT_LIFETIME;
        }
        if (!array_key_exists('automatic_cleaning_factor', $options)) {
            $options['automatic_cleaning_factor'] = 0;
        }
        $options['type'] =
            isset($cacheOptions['frontend']) ? $cacheOptions['frontend'] : 'Magento\Framework\Cache\Core';
        return $options;
    }
}
