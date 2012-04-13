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
 * API2 role list for admin user permissions
 *
 * @category   Mage
 * @package    Mage_Api2
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api2_Block_Adminhtml_Permissions_User_Edit_Tab_Roles
    extends Mage_Adminhtml_Block_Widget_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Selected API2 roles for grid
     *
     * @var array
     */
    protected $_selectedRoles;

    /**
     * Constructor
     * Prepare grid parameters
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('api2_roles_section')
            ->setDefaultSort('sort_order')
            ->setDefaultDir(Varien_Db_Select::SQL_ASC)
            ->setTitle($this->__('REST Roles Information'))
            ->setUseAjax(true);
    }

    /**
     * Prepare grid collection object
     *
     * @return Mage_Api2_Block_Adminhtml_Permissions_User_Edit_Tab_Roles
     */
    protected function _prepareCollection()
    {
        /** @var $collection Mage_Api2_Model_Resource_Acl_Global_Role_Collection */
        $collection = Mage::getResourceModel('Mage_Api2_Model_Resource_Acl_Global_Role_Collection');
        $collection->addFieldToFilter('entity_id', array('nin' => Mage_Api2_Model_Acl_Global_Role::getSystemRoles()));

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * @return Mage_Api2_Block_Adminhtml_Permissions_User_Edit_Tab_Roles
     */
    protected function _prepareColumns()
    {
        $this->addColumn('assigned_user_role', array(
            'header_css_class' => 'a-center',
            'header'    => $this->__('Assigned'),
            'type'      => 'radio',
            'html_name' => 'api2_roles[]',
            'values'    => $this->_getSelectedRoles(),
            'align'     => 'center',
            'index'     => 'entity_id'
        ));

        $this->addColumn('role_name', array(
            'header'    => $this->__('Role Name'),
            'index'     => 'role_name'
        ));

        return parent::_prepareColumns();
    }

    /**
     * Add custom column filter to collection
     *
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return Mage_Api2_Block_Adminhtml_Permissions_User_Edit_Tab_Roles
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'assigned_user_role') {
            $userRoles = $this->_getSelectedRoles();
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $userRoles));
            } elseif (!empty($userRoles)) {
                $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $userRoles));
            } else {
                $this->getCollection();
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * Get selected API2 roles for grid
     *
     * @return array
     */
    protected function _getSelectedRoles()
    {
        if (null === $this->_selectedRoles) {
            $userRoles = array();

            /* @var $user Mage_Admin_Model_User */
            $user = Mage::registry('permissions_user');
            if ($user->getId()) {
                /** @var $collection Mage_Api2_Model_Resource_Acl_Global_Role_Collection */
                $collection = Mage::getResourceModel('Mage_Api2_Model_Resource_Acl_Global_Role_Collection');
                $collection->addFilterByAdminId($user->getId());

                $userRoles = $collection->getAllIds();
            }

            $this->_selectedRoles = $userRoles;
        }

        return $this->_selectedRoles;
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('REST Role');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('REST Role');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Get controller action url for grid ajax actions
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            '*/api2_role/rolesGrid',
            array('user_id' => Mage::registry('permissions_user')->getUserId())
        );
    }
}
