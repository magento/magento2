<?php
/**
 * Primary application config (app/etc/*.xml)
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
class Mage_Core_Model_Config_Primary extends Mage_Core_Model_Config_Base
{
    /**
     * Install date xpath
     */
    const XML_PATH_INSTALL_DATE = 'global/install/date';

    /**
     * Configuration template for the application installation date
     */
    const CONFIG_TEMPLATE_INSTALL_DATE = '<config><global><install><date>%s</date></install></global></config>';

    /**
     * Application installation timestamp
     *
     * @var int|null
     */
    protected $_installDate;

    /**
     * @var Mage_Core_Model_Config_Loader_Primary
     */
    protected $_loader;

    /**
     * Application parameter list
     *
     * @var array
     */
    protected $_params;

    /**
     * Directory list
     *
     * @var Mage_Core_Model_Dir
     */
    protected $_dir;

    /**
     * @param string $baseDir
     * @param array $params
     * @param Mage_Core_Model_Dir $dir
     * @param Mage_Core_Model_Config_LoaderInterface $loader
     */
    public function __construct(
        $baseDir, array $params, Mage_Core_Model_Dir $dir = null, Mage_Core_Model_Config_LoaderInterface $loader = null
    ) {
        parent::__construct('<config/>');
        $this->_params = $params;
        $this->_dir = $dir ?: new Mage_Core_Model_Dir(
            $baseDir,
            $this->getParam(Mage::PARAM_APP_URIS, array()),
            $this->getParam(Mage::PARAM_APP_DIRS, array())
        );
        Magento_Autoload_IncludePath::addIncludePath(array(
            $this->_dir->getDir(Mage_Core_Model_Dir::GENERATION)
        ));

        $this->_loader = $loader ?: new Mage_Core_Model_Config_Loader_Primary(
            new Mage_Core_Model_Config_Loader_Local(
                $this->_dir->getDir(Mage_Core_Model_Dir::CONFIG),
                $this->getParam(Mage::PARAM_CUSTOM_LOCAL_CONFIG),
                $this->getParam(Mage::PARAM_CUSTOM_LOCAL_FILE)
            ),
            $this->_dir->getDir(Mage_Core_Model_Dir::CONFIG)
        );
        $this->_loader->load($this);
        $this->_loadInstallDate();
    }

    /**
     * Get init param
     *
     * @param string $name
     * @param mixed $defaultValue
     * @return mixed
     */
    public function getParam($name, $defaultValue = null)
    {
        return isset($this->_params[$name]) ? $this->_params[$name] : $defaultValue;
    }

    /**
     * Get application init params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
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
     * Retrieve directories
     *
     * @return Mage_Core_Model_Dir
     */
    public function getDirectories()
    {
        return $this->_dir;
    }

    /**
     * Reinitialize primary configuration
     */
    public function reinit()
    {
        $this->loadString('<config/>');
        $this->_loader->load($this);
        $this->_loadInstallDate();
    }

    /**
     * Retrieve class definition config
     *
     * @return string
     */
    public function getDefinitionPath()
    {
        $pathInfo = (array) $this->getNode('global/di/definitions');
        if (isset($pathInfo['path'])) {
            return $pathInfo['path'];
        } else if (isset($pathInfo['relativePath'])) {
            return $this->_dir->getDir(Mage_Core_Model_Dir::ROOT) . DIRECTORY_SEPARATOR . $pathInfo['relativePath'];
        } else {
            return $this->_dir->getDir(Mage_Core_Model_Dir::DI);
        }
    }

    /**
     * Retrieve definition format
     *
     * @return string
     */
    public function getDefinitionFormat()
    {
        return (string) $this->getNode('global/di/definitions/format');
    }

    /**
     * Configure object manager
     *
     * @param Magento_ObjectManager $objectManager
     */
    public function configure(Magento_ObjectManager $objectManager)
    {
        Magento_Profiler::start('initial');

        $objectManager->configure(array(
            'Mage_Core_Model_Config_Loader_Local' => array(
                'parameters' => array(
                    'configDirectory' => $this->_dir->getDir(Mage_Core_Model_Dir::CONFIG),
                )
            ),
            'Mage_Core_Model_Cache_Frontend_Factory' => array(
                'parameters' => array(
                    'decorators' => $this->_getCacheFrontendDecorators(),
                )
            ),
        ));

        $dynamicConfigurators = $this->getNode('global/configurators');
        if ($dynamicConfigurators) {
            $dynamicConfigurators = $dynamicConfigurators->asArray();
            if (count($dynamicConfigurators)) {
                foreach ($dynamicConfigurators as $configuratorClass) {
                    /** @var $dynamicConfigurator Mage_Core_Model_ObjectManager_DynamicConfigInterface*/
                    $dynamicConfigurator = $objectManager->create($configuratorClass);
                    $objectManager->configure($dynamicConfigurator->getConfiguration());
                }
            }
        }
        Magento_Profiler::stop('initial');
        Magento_Profiler::start('global_primary');
        $diConfig = $this->getNode('global/di');
        if ($diConfig) {
            $objectManager->configure($diConfig->asArray());
        }

        Magento_Profiler::stop('global_primary');
    }

    /**
     * Retrieve cache frontend decorators configuration
     *
     * @return array
     */
    protected function _getCacheFrontendDecorators()
    {
        $result = array();
        // mark all cache entries with a special tag to be able to clean only cache belonging to the application
        $result[] = array(
            'class' => 'Magento_Cache_Frontend_Decorator_TagScope',
            'parameters' => array('tag' => 'MAGE'),
        );
        if (Magento_Profiler::isEnabled()) {
            $result[] = array(
                'class' => 'Magento_Cache_Frontend_Decorator_Profiler',
                'parameters' => array('backendPrefixes' => array('Zend_Cache_Backend_', 'Varien_Cache_Backend_')),
            );
        }
        return $result;
    }
}
