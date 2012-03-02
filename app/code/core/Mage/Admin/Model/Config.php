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
     * Load config from merged adminhtml.xml files
     */
    public function __construct()
    {
        parent::__construct();
        $this->setCacheId('adminhtml_acl_menu_config');

        /* @var $adminhtmlConfig Varien_Simplexml_Config */
        $adminhtmlConfig = Mage::app()->loadCache($this->getCacheId());
        if ($adminhtmlConfig) {
            $this->_adminhtmlConfig = new Varien_Simplexml_Config($adminhtmlConfig);
        } else {
            $adminhtmlConfig = new Varien_Simplexml_Config;
            $adminhtmlConfig->loadString('<?xml version="1.0"?><config></config>');
            Mage::getConfig()->loadModulesConfiguration('adminhtml.xml', $adminhtmlConfig);
            $this->_adminhtmlConfig = $adminhtmlConfig;

            if (Mage::app()->useCache('config')) {
                Mage::app()->saveCache($adminhtmlConfig->getXmlString(), $this->getCacheId(),
                    array(Mage_Core_Model_Config::CACHE_TAG));
            }
        }
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
        return Mage::helper($moduleName)->__((string)$menuNode->title);
    }
}
