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
 * @package     Mage_User
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_User_Block_Role_Edit extends Mage_Backend_Block_Widget_Tabs
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('role_info_tabs');
        $this->setDestElementId('role_edit_form');
        $this->setTitle(Mage::helper('Mage_User_Helper_Data')->__('Role Information'));
    }

    protected function _prepareLayout()
    {
        $role = Mage::registry('current_role');

        $this->addTab(
            'info',
            $this->getLayout()
                ->createBlock('Mage_User_Block_Role_Tab_Info')
                ->setRole($role)
                ->setActive(true)
        );

        if ($role->getId()) {
            $this->addTab('roles', array(
                'label'     => Mage::helper('Mage_User_Helper_Data')->__('Role Users'),
                'title'     => Mage::helper('Mage_User_Helper_Data')->__('Role Users'),
                'content'   => $this->getLayout()
                    ->createBlock('Mage_User_Block_Role_Tab_Users', 'role.users.grid')
                    ->toHtml(),
            ));
        }

        return parent::_prepareLayout();
    }
}
