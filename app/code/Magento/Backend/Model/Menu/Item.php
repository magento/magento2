<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Model\Menu;

use Magento\Backend\Model\Menu;
use Magento\Store\Model\ScopeInterface;

/**
 * Menu item. Should be used to create nested menu structures with \Magento\Backend\Model\Menu
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Item
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
     * @var string
     */
    protected $_moduleName;

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
     * @var \Magento\Framework\AuthorizationInterface
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
     * @var Menu
     */
    protected $_submenu;

    /**
     * @var \Magento\Backend\Model\MenuFactory
     */
    protected $_menuFactory;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_urlModel;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Backend\Model\Menu\Item\Validator
     */
    protected $_validator;

    /**
     * Serialized submenu string
     *
     * @var string
     * @deprecated
     */
    protected $_serializedSubmenu;

    /**
     * Module list
     *
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $_moduleManager;

    /**
     * @param Item\Validator $validator
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Backend\Model\MenuFactory $menuFactory
     * @param \Magento\Backend\Model\UrlInterface $urlModel
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Model\Menu\Item\Validator $validator,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Backend\Model\MenuFactory $menuFactory,
        \Magento\Backend\Model\UrlInterface $urlModel,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_validator = $validator;
        $this->_validator->validate($data);
        $this->_moduleManager = $moduleManager;
        $this->_acl = $authorization;
        $this->_scopeConfig = $scopeConfig;
        $this->_menuFactory = $menuFactory;
        $this->_urlModel = $urlModel;
        $this->_moduleList = $moduleList;
        $this->populateFromArray($data);
    }

    /**
     * Retrieve argument element, or default value
     *
     * @param array $array
     * @param string $key
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
        return (null !== $this->_submenu) && (bool)$this->_submenu->count();
    }

    /**
     * Retrieve submenu
     *
     * @return Menu
     */
    public function getChildren()
    {
        if (!$this->_submenu) {
            $this->_submenu = $this->_menuFactory->create();
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
        if ((bool)$this->_action) {
            return $this->_urlModel->getUrl((string)$this->_action, ['_cache_secret_key' => true]);
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
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setAction($action)
    {
        $this->_validator->validateParam('action', $action);
        $this->_action = $action;
        return $this;
    }

    /**
     * Check whether item has javascript callback on click
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
     * @return $this
     * @throws \InvalidArgumentException
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
        return (bool)$this->_tooltip;
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
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setTooltip($tooltip)
    {
        $this->_validator->validateParam('toolTip', $tooltip);
        $this->_tooltip = $tooltip;
        return $this;
    }

    /**
     * Set Item module
     *
     * @param string $module
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setModule($module)
    {
        $this->_validator->validateParam('module', $module);
        $this->_moduleName = $module;
        return $this;
    }

    /**
     * Set Item module dependency
     *
     * @param string $moduleName
     * @return $this
     * @throws \InvalidArgumentException
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
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setConfigDependency($configPath)
    {
        $this->_validator->validateParam('dependsOnConfig', $configPath);
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
        return !$this->_moduleManager->isOutputEnabled(
            $this->_moduleName
        ) || !$this->_isModuleDependenciesAvailable() || !$this->_isConfigDependenciesAvailable();
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
            return $this->_moduleList->has($module);
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
            return $this->_scopeConfig->isSetFlag((string)$this->_dependsOnConfig, ScopeInterface::SCOPE_STORE);
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
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get menu item data represented as an array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'parent_id' => $this->_parentId,
            'module_name' => $this->_moduleName,
            'sort_index' => $this->_sortIndex,
            'depends_on_config' => $this->_dependsOnConfig,
            'id' => $this->_id,
            'resource' => $this->_resource,
            'path' => $this->_path,
            'action' => $this->_action,
            'depends_on_module' => $this->_dependsOnModule,
            'tooltip' => $this->_tooltip,
            'title' => $this->_title,
            'sub_menu' => isset($this->_submenu) ? $this->_submenu->toArray() : null
        ];
    }

    /**
     * Populate the menu item with data from array
     *
     * @param array $data
     * @return void
     */
    public function populateFromArray(array $data)
    {
        $this->_parentId = $this->_getArgument($data, 'parent_id');
        $this->_moduleName = $this->_getArgument($data, 'module_name', 'Magento_Backend');
        $this->_sortIndex = $this->_getArgument($data, 'sort_index');
        $this->_dependsOnConfig = $this->_getArgument($data, 'depends_on_config');
        $this->_id = $this->_getArgument($data, 'id');
        $this->_resource = $this->_getArgument($data, 'resource');
        $this->_path = $this->_getArgument($data, 'path', '');
        $this->_action = $this->_getArgument($data, 'action');
        $this->_dependsOnModule = $this->_getArgument($data, 'depends_on_module');
        $this->_tooltip = $this->_getArgument($data, 'tooltip', '');
        $this->_title = $this->_getArgument($data, 'title');
        if (isset($data['sub_menu'])) {
            $menu = $this->_menuFactory->create();
            $menu->populateFromArray($data['sub_menu']);
            $this->_submenu = $menu;
        } else {
            $this->_submenu = null;
        }
    }
}
