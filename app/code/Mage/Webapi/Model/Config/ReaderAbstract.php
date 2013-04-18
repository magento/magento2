<?php
/**
 * Abstract config data reader.
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
abstract class Mage_Webapi_Model_Config_ReaderAbstract
{
    /**
     * Cache ID for resource config.
     */
    const CONFIG_CACHE_ID = 'API-RESOURCE-CACHE';

    /**
     * Pattern for API action controllers class name.
     */
    const RESOURCE_CLASS_PATTERN = '/^(.*)_(.*)_Controller_Webapi(_.*)+$/';

    /**
     * @var Zend\Code\Scanner\DirectoryScanner
     */
    protected $_directoryScanner;

    /**
     * @var Mage_Webapi_Model_Config_Reader_ClassReflectorAbstract
     */
    protected $_classReflector;

    /**
     * @var Mage_Core_Model_Config
     */
    protected $_applicationConfig;

    /**
     * @var Mage_Core_Model_CacheInterface
     */
    protected $_cache;

    /**
     * @var array
     */
    protected $_data = array();

    /**
     * Construct config reader.
     *
     * @param Mage_Webapi_Model_Config_Reader_ClassReflectorAbstract $classReflector
     * @param Mage_Core_Model_Config $appConfig
     * @param Mage_Core_Model_CacheInterface $cache
     */
    public function __construct(
        Mage_Webapi_Model_Config_Reader_ClassReflectorAbstract $classReflector,
        Mage_Core_Model_Config $appConfig,
        Mage_Core_Model_CacheInterface $cache
    ) {
        $this->_classReflector = $classReflector;
        $this->_applicationConfig = $appConfig;
        $this->_cache = $cache;
    }

    /**
     * Retrieve cache ID.
     *
     * @return string
     */
    abstract public function getCacheId();

    /**
     * Get current directory scanner. Initialize if it was not initialized previously.
     *
     * @return Zend\Code\Scanner\DirectoryScanner
     */
    public function getDirectoryScanner()
    {
        if (!$this->_directoryScanner) {
            $this->_directoryScanner = new Zend\Code\Scanner\DirectoryScanner();
            /** @var Mage_Core_Model_Config_Element $module */
            foreach ($this->_applicationConfig->getNode('modules')->children() as $moduleName => $module) {
                if ($module->is('active')) {
                    /** Invalid type is specified to retrieve path to module directory. */
                    $moduleDir = $this->_applicationConfig->getModuleDir('invalid_type', $moduleName);
                    $directory = $moduleDir . DS . 'Controller' . DS . 'Webapi';
                    if (is_dir($directory)) {
                        $this->_directoryScanner->addDirectory($directory);
                    }
                }
            }
        }

        return $this->_directoryScanner;
    }

    /**
     * Set directory scanner object.
     *
     * @param Zend\Code\Scanner\DirectoryScanner $directoryScanner
     */
    public function setDirectoryScanner(Zend\Code\Scanner\DirectoryScanner $directoryScanner)
    {
        $this->_directoryScanner = $directoryScanner;
    }

    /**
     * Read configuration data from the action controllers files using class reflector.
     *
     * @throws InvalidArgumentException
     * @throws LogicException
     * @return array
     */
    public function getData()
    {
        if (!$this->_data && !$this->_loadDataFromCache()) {
            /** @var \Zend\Code\Scanner\FileScanner $file */
            foreach ($this->getDirectoryScanner()->getFiles(true) as $file) {
                $filename = $file->getFile();
                $classes = $file->getClasses();
                if (count($classes) > 1) {
                    throw new LogicException(sprintf(
                        'There can be only one class in the "%s" controller file .',
                        $filename
                    ));
                }
                /** @var \Zend\Code\Scanner\ClassScanner $class */
                $class = current($classes);
                $className = $class->getName();
                if (preg_match(self::RESOURCE_CLASS_PATTERN, $className)) {
                    $classData = $this->_classReflector->reflectClassMethods($className);
                    $this->_addData($classData);
                }
            }
            $postReflectionData = $this->_classReflector->getPostReflectionData();
            $this->_addData($postReflectionData);

            if (!isset($this->_data['resources'])) {
                throw new LogicException('Cannot populate config - no action controllers were found.');
            }

            $this->_saveDataToCache();
        }

        return $this->_data;
    }

    /**
     * Add data to reader.
     *
     * @param array $data
     */
    protected function _addData($data)
    {
        $this->_data = array_merge_recursive($this->_data, $data);
    }

    /**
     * Load config data from cache.
     *
     * @return bool Return true on successful load; false otherwise
     */
    protected function _loadDataFromCache()
    {
        $isLoaded = false;
        if ($this->_cache->canUse(Mage_Webapi_Model_ConfigAbstract::WEBSERVICE_CACHE_NAME)) {
            $cachedData = $this->_cache->load($this->getCacheId());
            if ($cachedData !== false) {
                $this->_data = unserialize($cachedData);
                $isLoaded = true;
            }
        }
        return $isLoaded;
    }

    /**
     * Save data to cache if it is enabled.
     */
    protected function _saveDataToCache()
    {
        if ($this->_cache->canUse(Mage_Webapi_Model_ConfigAbstract::WEBSERVICE_CACHE_NAME)) {
            $this->_cache->save(
                serialize($this->_data),
                $this->getCacheId(),
                array(Mage_Webapi_Model_ConfigAbstract::WEBSERVICE_CACHE_TAG)
            );
        }
    }
}
