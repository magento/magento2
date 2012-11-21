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
 * @package     Mage_Api2
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Block for rendering resources list tab
 *
 * @category   Mage
 * @package    Mage_Api2
 * @author     Magento Core Team <core@magentocommerce.com>
 * @method Mage_Api2_Model_Acl_Global_Role getRole()
 * @method Mage_Api2_Block_Adminhtml_Roles_Tab_Resources setRole(Mage_Api2_Model_Acl_Global_Role $role)
 */
class Mage_Api2_Block_Adminhtml_Roles_Tab_Resources extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Role model
     *
     * @var Mage_Api2_Model_Acl_Global_Role
     */
    protected $_role;

    /**
     * Tree model
     *
     * @var Mage_Api2_Model_Acl_Global_Rule_Tree
     */
    protected $_treeModel = false;

    /**
     * Constructor
     */
    public function _construct()
    {
        parent::_construct();

        $this->setId('api2_role_section_resources')
                ->setData('default_dir', Varien_Db_Select::SQL_ASC)
                ->setData('default_sort', 'sort_order')
                ->setData('title', Mage::helper('Mage_Api2_Helper_Data')->__('Api Rules Information'))
                ->setData('use_ajax', true);

        $options = array('type' => Mage_Api2_Model_Acl_Global_Rule_Tree::TYPE_PRIVILEGE);
        $this->_treeModel = Mage::getModel(
            'Mage_Api2_Model_Acl_Global_Rule_Tree', array('options' => $options)
        );
    }

    /**
     * Get Json Representation of Resource Tree
     *
     * @return string
     */
    public function getResTreeJson()
    {
        $this->_prepareTreeModel();
        /** @var $helper Mage_Core_Helper_Data */
        $helper = Mage::helper('Mage_Core_Helper_Data');
        return $helper->jsonEncode($this->_treeModel->getTreeResources());
    }

    /**
     * Prepare tree model
     *
     * @return Mage_Api2_Block_Adminhtml_Roles_Tab_Resources
     */
    public function _prepareTreeModel()
    {
        $role = $this->getRole();
        if ($role) {
            $permissionModel = $role->getPermissionModel();
            $permissionModel->setFilterValue($role);
            $this->_treeModel->setResourcesPermissions($permissionModel->getResourcesPermissions());
        } else {
            $role = Mage::getModel('Mage_Api2_Model_Acl_Global_Role');
        }
        $this->_treeModel->setRole($role);
        return $this;
    }

    /**
     * Check if everything is allowed
     *
     * @return boolean
     */
    public function getEverythingAllowed()
    {
        $this->_prepareTreeModel();
        return $this->_treeModel->getEverythingAllowed();
    }

    /**
     * Get tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('Mage_Api2_Helper_Data')->__('Role API Resources');
    }

    /**
     * Get tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Whether tab is available
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Whether tab is hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
