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

namespace Magento\Core\Model;

/**
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Config implements \Magento\Core\Model\ConfigInterface
{
    /**
     * Config cache tag
     */
    const CACHE_TAG = 'CONFIG';

    /**
     * Default configuration scope
     */
    const SCOPE_DEFAULT = 'default';

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
     * Object manager
     *
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * Configuration storage
     *
     * @var \Magento\Core\Model\Config\StorageInterface
     */
    protected $_storage;

    /**
     * Configuration data container
     *
     * @var \Magento\Core\Model\ConfigInterface
     */
    protected $_config;

    /**
     * Module configuration reader
     *
     * @var \Magento\Core\Model\Config\Modules\Reader
     */
    protected $_moduleReader;

    /**
     * @var \Magento\Config\ScopeInterface
     */
    protected $_configScope;

    /**
     * @var \Magento\App\ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @var \Magento\Core\Model\Config\SectionPool
     */
    protected $_sectionPool;

    /**
     * @var \Magento\Core\Model\Resource\Store\Collection
     */
    protected $_storeCollection;

    /**
     * @param \Magento\Core\Model\ObjectManager           $objectManager
     * @param \Magento\Core\Model\Config\StorageInterface $storage
     * @param \Magento\Core\Model\Config\Modules\Reader   $moduleReader
     * @param \Magento\App\ModuleListInterface     $moduleList
     * @param \Magento\Config\ScopeInterface              $configScope
     * @param \Magento\Core\Model\Config\SectionPool      $sectionPool
     */
    public function __construct(
        \Magento\Core\Model\ObjectManager $objectManager,
        \Magento\Core\Model\Config\StorageInterface $storage,
        \Magento\Core\Model\Config\Modules\Reader $moduleReader,
        \Magento\App\ModuleListInterface $moduleList,
        \Magento\Config\ScopeInterface $configScope,
        \Magento\Core\Model\Config\SectionPool $sectionPool
    ) {
        \Magento\Profiler::start('config_load');
        $this->_objectManager = $objectManager;
        $this->_storage = $storage;
        $this->_config = $this->_storage->getConfiguration();
        $this->_moduleReader = $moduleReader;
        $this->_moduleList = $moduleList;
        $this->_configScope = $configScope;
        $this->_sectionPool = $sectionPool;
        \Magento\Profiler::stop('config_load');
    }

    /**
     * Returns node found by the $path and scope info
     *
     * @param   string $path
     * @return \Magento\Core\Model\Config\Element
     * @deprecated
     */
    public function getNode($path = null)
    {
        return $this->_config->getNode($path);
    }

    /**
     * Retrieve config value by path and scope
     *
     * @param string $path
     * @param string $scope
     * @param string $scopeCode
     * @return mixed
     */
    public function getValue($path = null, $scope = 'default', $scopeCode = null)
    {
        return $this->_sectionPool->getSection($scope, $scopeCode)->getValue($path);
    }

    /**
     * Set config value in the corresponding config scope
     *
     * @param string $path
     * @param mixed $value
     * @param string $scope
     * @param null|string $scopeCode
     */
    public function setValue($path, $value, $scope = 'default', $scopeCode = null)
    {
        $this->_sectionPool->getSection($scope, $scopeCode)->setValue($path, $value);
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
        $this->_config->setNode($path, $value, $overwrite);
    }

    /**
     * Get allowed areas
     *
     * @return array
     */
    public function getAreas()
    {
        return $this->_allowedAreas;
    }

    /**
     * Identify front name of the requested area. Return current area front name if area code is not specified.
     *
     * @param string|null $areaCode
     * @return string
     * @throws \LogicException If front name is not defined.
     */
    public function getAreaFrontName($areaCode = null)
    {
        $areaCode = empty($areaCode) ? $this->_configScope->getCurrentScope() : $areaCode;
        $areaConfig = $this->getAreaConfig($areaCode);
        if (!isset($areaConfig['frontName'])) {
            throw new \LogicException(sprintf(
                'Area "%s" must have front name defined in the application config.',
                $areaCode
            ));
        }
        return $areaConfig['frontName'];
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
     * Retrieve store Ids for $path with checking
     *
     * if empty $allowValues then retrieve all stores values
     *
     * return array($storeId => $pathValue)
     *
     * @param string $path
     * @param array $allowedValues
     * @param string $keyAttribute
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getStoresConfigByPath($path, $allowedValues = array(), $keyAttribute = 'id')
    {
        // @todo inject custom store collection that corresponds to the following requirements
        if (is_null($this->_storeCollection)) {
            $this->_storeCollection = $this->_objectManager->create('Magento\Core\Model\Resource\Store\Collection');
            $this->_storeCollection->setLoadDefault(true);
        }
        $storeValues = array();
        /** @var $store \Magento\Core\Model\Store */
        foreach ($this->_storeCollection as $store) {
            switch ($keyAttribute) {
                case 'id':
                    $key = $store->getId();
                    break;
                case 'code':
                    $key = $store->getCode();
                    break;
                case 'name':
                    $key = $store->getName();
                    break;
                default:
                    throw new \InvalidArgumentException("'{$keyAttribute}' cannot be used as a key.");
                    break;
            }

            $value = $this->getValue($path, 'store', $store->getCode());
            if (empty($allowedValues)) {
                $storeValues[$key] = $value;
            } elseif (in_array($value, $allowedValues)) {
                $storeValues[$key] = $value;
            }
        }
        return $storeValues;
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
            foreach ($this->_moduleList->getModules() as $module) {
                $moduleName = $module['name'];
                $module = strtolower($moduleName);
                $this->_moduleNamespaces[substr($module, 0, strpos($module, '_'))][$module] = $moduleName;
            }
        }

        $explodeString = (strpos($name, \Magento\Autoload\IncludePath::NS_SEPARATOR) === false) ?
            '_' :  \Magento\Autoload\IncludePath::NS_SEPARATOR;
        $name = explode($explodeString, strtolower($name));

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
     * @return \Magento\Core\Model\Config
     */
    public function reinit()
    {
        $this->_sectionPool->clean();
    }

    /**
     * Remove configuration cache
     */
    public function removeCache()
    {
        $this->_storage->removeCache();
    }

    /**
     * Reload xml configuration data
     * @deprecated must be removed after Installation logic is removed from application
     */
    public function reloadConfig()
    {
        $this->_storage->removeCache();
        $this->_config = $this->_storage->getConfiguration();
    }
}
