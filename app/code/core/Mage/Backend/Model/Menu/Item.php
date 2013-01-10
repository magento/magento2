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
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Menu item. Should be used to create nested menu structures with Mage_Backend_Model_Menu
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Mage_Backend_Model_Menu_Item
{
    /**
     * Menu item id
     *
     * @var string
     */
    protected $_id;

    /**
     * Menu item title
     *
     * @var string
     */
    protected $_title;

    /**
     * Module of menu item
     *
     * @var Mage_Core_Helper_Abstract
     */
    protected $_moduleHelper;

    /**
     * Module helper name
     *
     * @var string
     */
    protected $_moduleHelperName;

    /**
     * Menu item sort index in list
     *
     * @var string
     */
    protected $_sortIndex = null;

    /**
     * Menu item action
     *
     * @var string
     */
    protected $_action = null;

    /**
     * Parent menu item id
     *
     * @var string
     */
    protected $_parentId = null;

    /**
     * Acl resource of menu item
     *
     * @var string
     */
    protected $_resource;

    /**
     * Item tooltip text
     *
     * @var string
     */
    protected $_tooltip;

    /**
     * Path from root element in tree
     *
     * @var string
     */
    protected $_path = '';

    /**
     * Acl
     *
     * @var Mage_Core_Model_Authorization
     */
    protected $_acl;

    /**
     * Module that item is dependent on
     *
     * @var string|null
     */
    protected $_dependsOnModule;

    /**
     * Global config option that item is dependent on
     *
     * @var string|null
     */
    protected $_dependsOnConfig;

    /**
     * Submenu item list
     *
     * @var Mage_Backend_Model_Menu
     */
    protected $_submenu;

    /**
     * @var Mage_Core_Model_Config
     */
    protected $_appConfig;

    /**
     * @var Mage_Backend_Model_Menu_Factory
     */
    protected $_menuFactory;

    /**
     * @var Mage_Backend_Model_Url
     */
    protected $_urlModel;

    /**
     * @var Mage_Core_Model_Store_Config
     */
    protected $_storeConfig;

    /**
     * @var Mage_Backend_Model_Menu_Item_Validator
     */
    protected $_validator;

    /**
     * Serialized submenu string
     *
     * @var string
     */
    protected $_serializedSubmenu;

    /**
     * @param Mage_Backend_Model_Menu_Item_Validator $validator
     * @param Mage_Core_Model_Authorization $authorization
     * @param Mage_Core_Model_Config $applicationConfig
     * @param Mage_Core_Model_Store_Config $storeConfig
     * @param Mage_Backend_Model_Menu_Factory $menuFactory
     * @param Mage_Backend_Model_Url $urlModel
     * @param Mage_Core_Helper_Abstract $helper
     * @param array $data
     */
    public function __construct(
        Mage_Backend_Model_Menu_Item_Validator $validator,
        Mage_Core_Model_Authorization $authorization,
        Mage_Core_Model_Config $applicationConfig,
        Mage_Core_Model_Store_Config $storeConfig,
        Mage_Backend_Model_Menu_Factory $menuFactory,
        Mage_Backend_Model_Url $urlModel,
        Mage_Core_Helper_Abstract $helper,
        array $data = array()
    ) {
        $this->_validator = $validator;
        $this->_validator->validate($data);

        $this->_acl = $authorization;
        $this->_appConfig = $applicationConfig;
        $this->_storeConfig = $storeConfig;
        $this->_menuFactory = $menuFactory;
        $this->_urlModel = $urlModel;
        $this->_moduleHelper = $helper;

        $this->_id = $data['id'];
        $this->_title = $data['title'];
        $this->_action = $this->_getArgument($data, 'action');
        $this->_resource = $this->_getArgument($data, 'resource');
        $this->_dependsOnModule = $this->_getArgument($data, 'dependsOnModule');
        $this->_dependsOnConfig = $this->_getArgument($data, 'dependsOnConfig');
        $this->_tooltip = $this->_getArgument($data, 'toolTip', '');
    }

    /**
     * Retrieve argument element, or default value
     *
     * @param array $array
     * @param mixed $key
     * @param mixed $defaultValue
     * @return mixed
     */
    protected function _getArgument(array $array, $key, $defaultValue = null)
    {
        return isset($array[$key]) ? $array[$key] : $defaultValue;
    }

    /**
     * Retrieve item id
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Check whether item has subnodes
     *
     * @return bool
     */
    public function hasChildren()
    {
        return !is_null($this->_submenu) && (bool) $this->_submenu->count();
    }

    /**
     * Retrieve submenu
     *
     * @return Mage_Backend_Model_Menu
     */
    public function getChildren()
    {
        if (!$this->_submenu) {
            $this->_submenu = $this->_menuFactory
                ->getMenuInstance();
        }
        return $this->_submenu;
    }

    /**
     * Retrieve menu item url
     *
     * @return string
     */
    public function getUrl()
    {
        if ((bool) $this->_action) {
            return $this->_urlModel->getUrl((string)$this->_action, array('_cache_secret_key' => true));
        }
        return '#';
    }

    /**
     * Retrieve menu item action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->_action;
    }

    /**
     * Set Item action
     *
     * @param string $action
     * @return Mage_Backend_Model_Menu_Item
     * @throws InvalidArgumentException
     */
    public function setAction($action)
    {
        $this->_validator->validateParam('action', $action);
        $this->_action = $action;
        return $this;
    }

    /**
     * Chechk whether item has javascript callback on click
     *
     * @return bool
     */
    public function hasClickCallback()
    {
        return $this->getUrl() == '#';
    }

    /**
     * Retrieve item click callback
     *
     * @return string
     */
    public function getClickCallback()
    {
        if ($this->getUrl() == '#') {
            return 'return false;';
        }
        return '';
    }

    /**
     * Retrieve tooltip text title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * Set Item title
     *
     * @param string $title
     * @return Mage_Backend_Model_Menu_Item
     * @throws InvalidArgumentException
     */
    public function setTitle($title)
    {
        $this->_validator->validateParam('title', $title);
        $this->_title = $title;
        return $this;
    }

    /**
     * Check whether item has tooltip text
     *
     * @return bool
     */
    public function hasTooltip()
    {
        return (bool) $this->_tooltip;
    }

    /**
     * Retrieve item tooltip text
     *
     * @return string
     */
    public function getTooltip()
    {
        return $this->_tooltip;
    }

    /**
     * Set Item tooltip
     *
     * @param string $tooltip
     * @return Mage_Backend_Model_Menu_Item
     * @throws InvalidArgumentException
     */
    public function setTooltip($tooltip)
    {
        $this->_validator->validateParam('toolTip', $tooltip);
        $this->_tooltip = $tooltip;
        return $this;
    }

    /**
     * Retrieve module helper object linked to item.
     * Should be used to translate item labels
     *
     * @return Mage_Core_Helper_Abstract
     */
    public function getModuleHelper()
    {
        return $this->_moduleHelper;
    }

    /**
     * Set Item module
     *
     * @param Mage_Core_Helper_Abstract $helper
     * @return Mage_Backend_Model_Menu_Item
     * @throws InvalidArgumentException
     */
    public function setModuleHelper(Mage_Core_Helper_Abstract $helper)
    {
        $this->_validator->validateParam('module', $helper);
        $this->_moduleHelper = $helper;
        return $this;
    }

    /**
     * Set Item module dependency
     *
     * @param string $moduleName
     * @return Mage_Backend_Model_Menu_Item
     * @throws InvalidArgumentException
     */
    public function setModuleDependency($moduleName)
    {
        $this->_validator->validateParam('dependsOnModule', $moduleName);
        $this->_dependsOnModule = $moduleName;
        return $this;
    }

    /**
     * Set Item config dependency
     *
     * @param string $configPath
     * @return Mage_Backend_Model_Menu_Item
     * @throws InvalidArgumentException
     */
    public function setConfigDependency($configPath)
    {
        $this->_validator->validateParam('depenedsOnConfig', $configPath);
        $this->_dependsOnConfig = $configPath;
        return $this;
    }

    /**
     * Check whether item is disabled. Disabled items are not shown to user
     *
     * @return bool
     */
    public function isDisabled()
    {
        return !$this->_moduleHelper->isModuleOutputEnabled()
            || !$this->_isModuleDependenciesAvailable()
            || !$this->_isConfigDependenciesAvailable();
    }

    /**
     * Check whether module that item depends on is active
     *
     * @return bool
     */
    protected function _isModuleDependenciesAvailable()
    {
        if ($this->_dependsOnModule) {
            $module = $this->_dependsOnModule;
            $modulesConfig = $this->_appConfig->getNode('modules');
            return ($modulesConfig->$module && $modulesConfig->$module->is('active'));
        }
        return true;
    }

    /**
     * Check whether config dependency is available
     *
     * @return bool
     */
    protected function _isConfigDependenciesAvailable()
    {
        if ($this->_dependsOnConfig) {
            return $this->_storeConfig->getConfigFlag((string)$this->_dependsOnConfig);
        }
        return true;
    }

    /**
     * Check whether item is allowed to the user
     *
     * @return bool
     */
    public function isAllowed()
    {
        try {
            return $this->_acl->isAllowed((string)$this->_resource);
        } catch (Exception $e) {
            return false;
        }
    }

    public function __sleep()
    {
        if (Mage::getIsSerializable()) {
            $this->_moduleHelperName = get_class($this->_moduleHelper);
            if ($this->_submenu) {
                $this->_serializedSubmenu = $this->_submenu->serialize();
            }
            return array(
                '_parentId',
                '_moduleHelperName',
                '_sortIndex',
                '_dependsOnConfig',
                '_id',
                '_resource',
                '_path',
                '_action',
                '_dependsOnModule',
                '_tooltip',
                '_title',
                '_serializedSubmenu'
            );
        } else {
            return array_keys(get_object_vars($this));
        }
    }

    public function __wakeup()
    {
        if (Mage::getIsSerializable()) {
            $this->_moduleHelper = Mage::helper($this->_moduleHelperName);
            $this->_validator = Mage::getSingleton('Mage_Backend_Model_Menu_Item_Validator');
            $this->_acl = Mage::getSingleton('Mage_Core_Model_Authorization');
            $this->_appConfig = Mage::getConfig();
            $this->_storeConfig =  Mage::getSingleton('Mage_Core_Model_Store_Config');
            $this->_menuFactory = Mage::getSingleton('Mage_Backend_Model_Menu_Factory');
            $this->_urlModel = Mage::getSingleton('Mage_Backend_Model_Url');
            if ($this->_serializedSubmenu) {
                $this->_submenu = $this->_menuFactory->getMenuInstance();
                $this->_submenu->unserialize($this->_serializedSubmenu);
            }
        }
    }
}
