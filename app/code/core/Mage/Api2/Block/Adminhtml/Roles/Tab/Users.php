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
 * @package     Mage_Adminhtml
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Block for rendering users list tab
 *
 * @category   Mage
 * @package    Mage_Api2
 * @author     Magento Core Team <core@magentocommerce.com>
 * @method Mage_Api2_Model_Acl_Global_Role getRole()
 * @method Mage_Api2_Block_Adminhtml_Roles_Tab_Users setRole(Mage_Api2_Model_Acl_Global_Role $role)
 * @method Mage_Api2_Block_Adminhtml_Roles_Tab_Users setUsers(array $users)
 * @method Mage_User_Model_Resource_User_Collection getCollection()
 */
class Mage_Api2_Block_Adminhtml_Roles_Tab_Users extends Mage_Adminhtml_Block_Widget_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Construct grid block
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('roleUsersGrid');
        $this->setData('use_ajax', true);
        $this->setSaveParametersInSession(true);
        $this->setDefaultSort('user_id')
            ->setDefaultDir(Varien_Db_Select::SQL_DESC);
        $this->setDefaultFilter(array('filter_in_role_users'=>1));
    }

    /**
     * Prepare collection
     *
     * @return Mage_Api2_Block_Adminhtml_Roles_Tab_Users
     */
    protected function _prepareCollection()
    {
        /** @var $collection Mage_User_Model_Resource_User_Collection */
        $collection = Mage::getModel('Mage_User_Model_User')->getCollection();
        $collection->getSelect()->joinLeft(
            array('acl' => $collection->getTable('api2_acl_user')),
            'acl.admin_id = main_table.user_id',
            'role_id'
        );
        if ($this->getRole() && $this->getRole()->getId()) {
            $collection->addFilter('acl.role_id', $this->getRole()->getId());
        }

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * Prepare columns
     *
     * @return Mage_Api2_Block_Adminhtml_Roles_Tab_Users
     */
    protected function _prepareColumns()
    {
        $this->addColumn('filter_in_role_users', array(
            'header_css_class' => 'a-center',
            'type'      => 'checkbox',
            'name'      => 'filter_in_role_users',
            'values'    => $this->getUsers(),
            'align'     => 'center',
            'index'     => 'user_id'
        ));

        $this->addColumn('user_id', array(
            'header' => Mage::helper('Mage_Api2_Helper_Data')->__('ID'), 'index' => 'user_id', 'align' => 'right', 'width' => '50px',
        ));

        $this->addColumn('username', array(
            'header' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('User Name'), 'align' => 'left', 'index' => 'username'
        ));

        $this->addColumn('firstname', array(
            'header' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('First Name'), 'align' => 'left', 'index' => 'firstname'
        ));

        $this->addColumn('lastname', array(
            'header' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Last Name'), 'align' => 'left', 'index' => 'lastname'
        ));

        return parent::_prepareColumns();
    }

    /**
     * Get grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/usersGrid', array('_current' => true));
    }

    /**
     * Get row URL
     *
     * @param Mage_Api2_Model_Acl_Global_Role $row
     * @return string|null
     */
    public function getRowUrl($row)
    {
        return null;
    }

    /**
     * Get tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('Mage_Api2_Helper_Data')->__('Role Users');
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
        return !$this->isHidden();
    }

    /**
     * Whether tab is hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return $this->getRole() && Mage_Api2_Model_Acl_Global_Role::isSystemRole($this->getRole());
    }

    /**
     * Render block only when not hidden
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->isHidden()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return Mage_Api2_Block_Adminhtml_Roles_Tab_Users
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'filter_in_role_users') {
            $inRoleIds = $this->getUsers();
            if (empty($inRoleIds)) {
                $inRoleIds = 0;
            }

            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('user_id', array('in' => $inRoleIds));
            } else {
                if($inRoleIds) {
                    $this->getCollection()->addFieldToFilter('user_id', array('nin' => $inRoleIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Get users
     *
     * @param bool $json
     * @return array|string
     */
    public function getUsers($json = false)
    {
        $users = $this->getData('users');

        if ($json) {
            if ($users === array()) {
                return '{}';
            }
            $jsonUsers = array();
            foreach($users as $usrId) {
                $jsonUsers[$usrId] = 0;
            }
            /** @var $helper Mage_Core_Helper_Data */
            $helper = Mage::helper('Mage_Core_Helper_Data');
            $result = $helper->jsonEncode((object) $jsonUsers);
        } else {
            $result = array_values($users);
        }

        return $result;
    }
}
