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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Adminhtml_Block_Api_User_Edit_Tab_Roles extends Mage_Adminhtml_Block_Widget_Grid
{

    protected function _construct()
    {
        parent::_construct();
        $this->setId('permissionsUserRolesGrid');
        $this->setDefaultSort('sort_order');
        $this->setDefaultDir('asc');
        //$this->setDefaultFilter(array('assigned_user_role'=>1));
        $this->setTitle(Mage::helper('Mage_Adminhtml_Helper_Data')->__('User Roles Information'));
        $this->setUseAjax(true);
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'assigned_user_role') {
            $userRoles = $this->_getSelectedRoles();
            if (empty($userRoles)) {
                $userRoles = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('role_id', array('in'=>$userRoles));
            }
            else {
                if($userRoles) {
                    $this->getCollection()->addFieldToFilter('role_id', array('nin'=>$userRoles));
                }
            }
        }
        else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('Mage_Api_Model_Resource_Role_Collection');
        $collection->setRolesFilter();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('assigned_user_role', array(
            'header_css_class' => 'a-center',
            'header'    => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Assigned'),
            'type'      => 'radio',
            'html_name' => 'roles[]',
            'values'    => $this->_getSelectedRoles(),
            'align'     => 'center',
            'index'     => 'role_id'
        ));

        /*$this->addColumn('role_id', array(
            'header'    =>Mage::helper('Mage_Adminhtml_Helper_Data')->__('Role ID'),
            'index'     =>'role_id',
            'align'     => 'right',
            'width'    => '50px'
        ));*/

        $this->addColumn('role_name', array(
            'header'    =>Mage::helper('Mage_Adminhtml_Helper_Data')->__('Role Name'),
            'index'     =>'role_name'
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/rolesGrid', array('user_id' => Mage::registry('api_user')->getUserId()));
    }

    protected function _getSelectedRoles($json=false)
    {
        if ( $this->getRequest()->getParam('user_roles') != "" ) {
            return $this->getRequest()->getParam('user_roles');
        }
        $uRoles = Mage::registry('api_user')->getRoles();
        if ($json) {
            $jsonRoles = Array();
            foreach($uRoles as $urid) $jsonRoles[$urid] = 0;
            return Mage::helper('Mage_Core_Helper_Data')->jsonEncode((object)$jsonRoles);
        } else {
            return $uRoles;
        }
    }

}
