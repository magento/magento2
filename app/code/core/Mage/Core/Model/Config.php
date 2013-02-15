<?php
/**
 * Application configuration object. Used to access configuration when application is initialized and installed.
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
class Mage_Core_Model_Config implements Mage_Core_Model_ConfigInterface
{
    /**
     * Config cache tag
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

    /**
     * Storage of validated secure urls
     *
     * @var array
     */
    protected $_secureUrlCache = array();

    /**
     * Active modules array per namespace
     *
     * @var array
     */
    private $_moduleNamespaces = null;

    /**
     * Areas allowed to use
     *
     * @var array
     */
    protected $_allowedAreas = null;

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
     * Configuration storage
     *
     * @var Mage_Core_Model_Config_StorageInterface
     */
    protected $_storage;

    /**
     * Configuration data container
     *
     * @var Mage_Core_Model_ConfigInterface
     */
    protected $_config;

    /**
     * Application object
     *
     * @var Mage_Core_Model_AppInterface
     */
    protected $_app;

    /**
     * Module configuration reader
     *
     * @var Mage_Core_Model_Config_Modules_Reader
     */
    protected $_moduleReader;

    /**
     * @var Mage_Core_Model_Config_InvalidatorInterface
     */
    protected $_invalidator;

    /**
     * @param Magento_ObjectManager $objectManager
     * @param Mage_Core_Model_Config_StorageInterface $storage
     * @param Mage_Core_Model_AppInterface $app
     * @param Mage_Core_Model_Config_Modules_Reader $moduleReader
     * @param Mage_Core_Model_Config_InvalidatorInterface $invalidator
     */
    public function __construct(
        Magento_ObjectManager $objectManager,
        Mage_Core_Model_Config_StorageInterface $storage,
        Mage_Core_Model_AppInterface $app,
        Mage_Core_Model_Config_Modules_Reader $moduleReader,
        Mage_Core_Model_Config_InvalidatorInterface $invalidator
    ) {
        Magento_Profiler::start('config_load');
        $this->_objectManager = $objectManager;
        $this->_app = $app;
        $this->_storage = $storage;
        $this->_config = $this->_storage->getConfiguration();
        $this->_moduleReader = $moduleReader;
        $this->_invalidator = $invalidator;
        $this->_objectManager->setConfiguration($this->getNode('global/di')->asArray());
        Magento_Profiler::stop('config_load');
    }

    /**
     * Load allowed areas from config
     *
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
     * Returns nodes found by xpath expression
     *
     * @param string $xpath
     * @return array
     */
    public function getXpath($xpath)
    {
        return $this->_config->getXpath($xpath);
    }

    /**
     * Returns node found by the $path and scope info
     *
     * @param   string $path
     * @param   string $scope
     * @param   string|int $scopeCode
     * @return Mage_Core_Model_Config_Element
     */
    public function getNode($path = null, $scope = '', $scopeCode = null)
    {
        if ($scope !== '') {
            if (('store' === $scope) || ('website' === $scope)) {
                $scope .= 's';
            }
            if (('default' !== $scope) && is_int($scopeCode)) {
                if ('stores' == $scope) {
                    $scopeCode = $this->_app->getStore($scopeCode)->getCode();
                } elseif ('websites' == $scope) {
                    $scopeCode = $this->_app->getWebsite($scopeCode)->getCode();
                } else {
                    Mage::throwException(
                        $this->_objectManager->get('Mage_Core_Helper_Data')
                            ->__('Unknown scope "%s".', $scope)
                    );
                }
            }
            $path = $scope . ($scopeCode ? '/' . $scopeCode : '' ) . (empty($path) ? '' : '/' . $path);
        }
        try {
            return $this->_config->getNode($path);
        } catch (Mage_Core_Model_Config_Cache_Exception $e) {
            $this->reinit();
            return $this->_config->getNode($path);
        }
    }

    /**
     * Create node by $path and set its value.
     *
     * @param string $path separated by slashes
     * @param string $value
     * @param bool $overwrite
     */
    public function setNode($path, $value, $overwrite = true)
    {
        try {
            $this->_config->setNode($path, $value, $overwrite);
        } catch (Mage_Core_Model_Config_Cache_Exception $e) {
            $this->reinit();
            $this->_config->setNode($path, $value, $overwrite);
        }
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
     * Get allowed areas
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
     * @throws InvalidArgumentException
     * @return array
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
     * Identify front name of the requested area. Return current area front name if area code is not specified.
     *
     * @param string|null $areaCode
     * @return string
     * @throws LogicException If front name is not defined.
     */
    public function getAreaFrontName($areaCode = null)
    {
        $areaCode = empty($areaCode) ? $this->getCurrentAreaCode() : $areaCode;
        $areaConfig = $this->getAreaConfig($areaCode);
        if (!isset($areaConfig['frontName'])) {
            throw new LogicException(sprintf(
                'Area "%s" must have front name defined in the application config.',
                $areaCode
            ));
        }
        return $areaConfig['frontName'];
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
     * Check if specified module is enabled
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
     * Get module directory by directory type
     *
     * @param   string $type
     * @param   string $moduleName
     * @return  string
     */
    public function getModuleDir($type, $moduleName)
    {
        return $this->_moduleReader->getModuleDir($type, $moduleName);
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
        $this->_moduleReader->setModuleDir($moduleName, $type, $path);
        return $this;
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
     * @param   string  $useAsKey
     * @return  array
     */
    public function getStoresConfigByPath($path, $allowValues = array(), $useAsKey = 'id')
    {
        $storeValues = array();
        $stores = $this->getNode('stores');
        /** @var $store Varien_Simplexml_Element */
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
                    $key = false;
                    break;
            }

            if ($key === false) {
                continue;
            }

            $pathValue = (string) $store->descend($path);

            if (empty($allowValues)) {
                $storeValues[$key] = $pathValue;
            } elseif (in_array($pathValue, $allowValues)) {
                $storeValues[$key] = $pathValue;
            }
        }

        return $storeValues;
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
        $config = $this->_objectManager->get('Mage_Core_Model_Config_Fieldset');
        $rootNode = $config->getNode($root . '/fieldsets');
        if (!$rootNode) {
            return null;
        }
        return $rootNode->$name ? $rootNode->$name->children() : null;
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
     * Get default server variables values
     *
     * @return array
     */
    public function getDistroBaseUrl()
    {
        if (isset($_SERVER['SCRIPT_NAME']) && isset($_SERVER['HTTP_HOST'])) {
            $secure = (!empty($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != 'off'))
                || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443');
            $scheme = ($secure ? 'https' : 'http') . '://' ;

            $hostArr = explode(':', $_SERVER['HTTP_HOST']);
            $host = $hostArr[0];
            $port = isset($hostArr[1]) && (!$secure && $hostArr[1] != 80 || $secure && $hostArr[1] != 443)
                ? ':'. $hostArr[1]
                : '';
            $path = Mage::getObjectManager()->get('Mage_Core_Controller_Request_Http')->getBasePath();

            return $scheme . $host . $port . rtrim($path, '/') . '/';
        }
        return 'http://localhost/';
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
        if (null === $this->_moduleNamespaces) {
            $this->_moduleNamespaces = array();
            /** @var $moduleConfig Varien_Simplexml_Element */
            foreach ($this->getXpath('modules/*') as $moduleConfig) {
                if ((string)$moduleConfig->active == 'true') {
                    $moduleName = $moduleConfig->getName();
                    $module = strtolower($moduleName);
                    $this->_moduleNamespaces[substr($module, 0, strpos($module, '_'))][$module] = $moduleName;
                }
            }
        }

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
     * Reinitialize configuration
     *
     * @return Mage_Core_Model_Config
     */
    public function reinit()
    {
        $this->removeCache();
        $this->_invalidator->invalidate();
        $this->_config = $this->_storage->getConfiguration();
    }

    /**
     * Get model class instance.
     *
     * Example:
     * $config->getModelInstance('Mage_Catalog_Model_Resource_Product')
     *
     * Will instantiate Mage_Catalog_Model_Resource_Product
     *
     * @param string $modelClass
     * @param array|object $constructArguments
     * @return Mage_Core_Model_Abstract|bool
     */
    public function getModelInstance($modelClass = '', $constructArguments = array())
    {
        if (class_exists($modelClass)) {
            Magento_Profiler::start('FACTORY:' . $modelClass);
            $obj = $this->_objectManager->create($modelClass, $constructArguments);
            Magento_Profiler::stop('FACTORY:' . $modelClass);
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
    public function getResourceModelInstance($modelClass='', $constructArguments=array())
    {
        return $this->getModelInstance($modelClass, $constructArguments);
    }

    /**
     * Remove configuration cache
     */
    public function removeCache()
    {
        /** @var $eventManager Mage_Core_Model_Event_Manager */
        $eventManager = $this->_objectManager->get('Mage_Core_Model_Event_Manager');
        $eventManager->dispatch('application_clean_cache', array('tags' => array(self::CACHE_TAG)));
        $this->_storage->removeCache();
    }
}
