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

class Mage_Adminhtml_Block_Permissions_Editroles extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('role_info_tabs');
        $this->setDestElementId('role_edit_form');
        $this->setTitle(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Role Information'));
    }

    protected function _prepareLayout()
    {
        $role = Mage::registry('current_role');

        $this->addTab(
            'info',
            $this->getLayout()
                ->createBlock('Mage_Adminhtml_Block_Permissions_Tab_Roleinfo')
                ->setRole($role)
                ->setActive(true)
        );
        $this->addTab(
            'account',
            $this->getLayout()
                ->createBlock('Mage_Adminhtml_Block_Permissions_Tab_Rolesedit', 'adminhtml.permissions.tab.rolesedit')
        );

        if ($role->getId()) {
            $this->addTab('roles', array(
                'label'     => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Role Users'),
                'title'     => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Role Users'),
                'content'   => $this->getLayout()
                    ->createBlock('Mage_Adminhtml_Block_Permissions_Tab_Rolesusers', 'role.users.grid')
                    ->toHtml(),
            ));
        }

        return parent::_prepareLayout();
    }
}
