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
 * @package     Mage_Admin
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Configuration for Admin model
 *
 * @category   Mage
 * @package    Mage_Admin
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Admin_Model_Config extends Varien_Simplexml_Config
{
    /**
     * adminhtml.xml merged config
     *
     * @var Varien_Simplexml_Config
     */
    protected $_adminhtmlConfig;

    /**
     * Main Application object
     *
     * @var Mage_Core_Model_App
     */
    protected $_app;

    /**
     * Main Application config
     *
     * @var Mage_Core_Model_Config
     */
    protected $_appConfig;

    /**
     * List of helpers by module
     *
     * @var array
     */
    protected $_helpers = array();

    /**
     * Load config from merged adminhtml.xml files
     * @param array $arguments
     */
    public function __construct(array $arguments = array())
    {
        $this->_app = isset($arguments['app']) ? $arguments['app'] : Mage::app();
        $this->_appConfig = isset($arguments['appConfig']) ? $arguments['appConfig'] : Mage::getConfig();
        if (isset($arguments['helpers'])) {
            $this->_helpers = $arguments['helpers'];
        }


        parent::__construct();
        $this->setCacheId('adminhtml_acl_menu_config');

        /* @var $adminhtmlConfig Varien_Simplexml_Config */
        $adminhtmlConfig = $this->_app->loadCache($this->getCacheId());
        if ($adminhtmlConfig) {
            $this->_adminhtmlConfig = new Varien_Simplexml_Config($adminhtmlConfig);
        } else {
            $adminhtmlConfig = new Varien_Simplexml_Config;
            $adminhtmlConfig->loadString('<?xml version="1.0"?><config></config>');
            $this->_appConfig->loadModulesConfiguration('adminhtml.xml', $adminhtmlConfig);
            $this->_adminhtmlConfig = $adminhtmlConfig;

            if ($this->_app->useCache('config')) {
                $this->_app->saveCache($adminhtmlConfig->getXmlString(), $this->getCacheId(),
                    array(Mage_Core_Model_Config::CACHE_TAG));
            }
        }
    }

    /**
     * Retrieve base helper by module
     *
     * @param string $module
     * @return Mage_Core_Helper_Abstract
     */
    protected function _getHelper($module)
    {
        if (isset($this->_helpers[$module])) {
            return $this->_helpers[$module];
        }
        return Mage::helper($module);
    }

    /**
     * Load Acl resources from config
     *
     * @param Mage_Admin_Model_Acl $acl
     * @param Mage_Core_Model_Config_Element $resource
     * @param string $parentName
     * @return Mage_Admin_Model_Config
     */
    public function loadAclResources(Mage_Admin_Model_Acl $acl, $resource = null, $parentName = null)
    {
        if (is_null($resource)) {
            $resource = $this->getAdminhtmlConfig()->getNode("acl/resources");
            $resourceName = null;
        } else {
            $resourceName = (is_null($parentName) ? '' : $parentName . '/') . $resource->getName();
            $acl->add(Mage::getModel('Mage_Admin_Model_Acl_Resource', $resourceName), $parentName);
        }

        if (isset($resource->all)) {
            $acl->add(Mage::getModel('Mage_Admin_Model_Acl_Resource', 'all'), null);
        }

        if (isset($resource->admin)) {
            $children = $resource->admin;
        } elseif (isset($resource->children)){
            $children = $resource->children->children();
        }



        if (empty($children)) {
            return $this;
        }

        foreach ($children as $res) {
            if (1 == $res->disabled) {
                continue;
            }
            $this->loadAclResources($acl, $res, $resourceName);
        }
        return $this;
    }

    /**
     * Retrieve Acl Resource Tree with module and path information
     *
     * @return Varien_Simplexml_Element
     */
    public function getAclResourceTree()
    {
        return $this->_walkResourceTree();
    }

    /**
     * Retrieve flat Acl Resource list with level information
     * @param bool $shortFormat
     * @return array
     */
    public function getAclResourceList($shortFormat = false)
    {
        return $this->_flattenResourceTree(null, null, 0, 'Mage_Backend', $shortFormat);
    }

    /**
     * Decorate acl resource tree
     *
     * @param  Varien_Simplexml_Element $resource
     * @param  null $parentName
     * @param  string $module
     * @return Varien_Simplexml_Element
     */
    protected function _walkResourceTree(Varien_Simplexml_Element $resource = null,
        $parentName = null, $module = 'Mage_Backend')
    {
        $resourceName = $parentName;
        if (is_null($resource)) {
            $resource = $this->getAdminhtmlConfig()->getNode('acl/resources');
            $resourceName = null;
            $level = -1;
        } else {
            if (!$this->_isServiceElement($resource)) {
                $resourceName = $this->_buildFullResourceName($resource, $parentName);
                //assigning module for its' children nodes
                if ($resource->getAttribute('module')) {
                    $module = (string)$resource->getAttribute('module');

                }
                $resource->addAttribute('aclpath', $resourceName);
                $resource->addAttribute('module_c', $module);
            }
        }

        //check children and run recursion if they exists
        $children = $resource->children();
        foreach ($children as $key => $child) {
            if (1 == $child->disabled) {
                $resource->{$key} = null;
                continue;
            }
            $this->_walkResourceTree($child, $resourceName, $module);
        }
        return $resource;
    }

    /**
     * Flatten acl resources tree
     *
     * @param null|Varien_Simplexml_Element $resource
     * @param null $parentName
     * @param int $level
     * @param string $module
     * @param bool $shortFormat
     * @return array
     */
    protected function _flattenResourceTree(Varien_Simplexml_Element $resource = null,
        $parentName = null, $level = 0, $module = 'Mage_Backend', $shortFormat = false)
    {
        $result = array();
        $resourceName = $parentName;
        if (is_null($resource)) {
            $resource = $this->getAdminhtmlConfig()->getNode('acl/resources');
            $resourceName = null;
            $level = -1;
        } else {
            if (!$this->_isServiceElement($resource)) {
                $resourceName = $this->_buildFullResourceName($resource, $parentName);

                if ($shortFormat) {
                    $result[] = $resourceName;
                } else {
                    if ($resource->getAttribute('module')) {
                        $module = (string)$resource->getAttribute('module');
                    }
                    $result[$resourceName]['name']  = $this->_getHelper($module)->__((string)$resource->title);
                    $result[$resourceName]['level'] = $level;
                }
            }
        }
        //check children and run recursion if they exists
        $children = $resource->children();
        foreach ($children as $key => $child) {
            if (1 == $child->disabled) {
                continue;
            }
            $result = array_merge(
                $this->_flattenResourceTree($child, $resourceName, $level + 1, $module, $shortFormat),
                $result
            );
        }
        return $result;
    }

    /**
     * Check whether provided element is a service element of Admin Xml configuration
     *
     * @param Varien_Simplexml_Element $resource
     * @return bool
     */
    protected function _isServiceElement(Varien_Simplexml_Element $resource)
    {
        return in_array($resource->getName(), array('title', 'sort_order', 'children', 'disabled'));
    }

    /**
     * Build acl resource name with path to parent
     *
     * @param Varien_Simplexml_Element $resource
     * @param string $path
     * @return string
     */
    protected function _buildFullResourceName(Varien_Simplexml_Element $resource, $path = null)
    {
        return (is_null($path) ? '' : $path . '/') . $resource->getName();
    }

    /**
     * Get acl assert config
     *
     * @param string $name
     * @return Mage_Core_Model_Config_Element|boolean
     */
    public function getAclAssert($name = '')
    {
        $asserts = $this->getNode("admin/acl/asserts");
        if ('' === $name) {
            return $asserts;
        }

        if (isset($asserts->$name)) {
            return $asserts->$name;
        }

        return false;
    }

    /**
     * Retrieve privilege set by name
     *
     * @param string $name
     * @return Mage_Core_Model_Config_Element|boolean
     */
    public function getAclPrivilegeSet($name = '')
    {
        $sets = $this->getNode("admin/acl/privilegeSets");
        if ('' === $name) {
            return $sets;
        }

        if (isset($sets->$name)) {
            return $sets->$name;
        }

        return false;
    }

    /**
     * Retrieve xml config
     *
     * @return Varien_Simplexml_Config
     */
    public function getAdminhtmlConfig()
    {
        return $this->_adminhtmlConfig;
    }

    /**
     * Get menu item label by item path
     *
     * @param string $path
     * @return string
     */
    public function getMenuItemLabel($path)
    {
        $moduleName = 'Mage_Adminhtml_Helper_Data';
        $menuNode = $this->getAdminhtmlConfig()->getNode('menu/' . str_replace('/', '/children/', trim($path, '/')));
        if ($menuNode->getAttribute('module')) {
            $moduleName = (string)$menuNode->getAttribute('module');
        }
        return $this->_getHelper($moduleName)->__((string)$menuNode->title);
    }
}
