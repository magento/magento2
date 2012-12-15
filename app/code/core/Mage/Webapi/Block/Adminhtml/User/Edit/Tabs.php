<?php
/**
 * Web API user edit page tabs.
 *
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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @method Varien_Object getApiUser() getApiUser()
 * @method Mage_Webapi_Block_Adminhtml_User_Edit_Tabs setApiUser() setApiUser(Varien_Object $apiUser)
 */
class Mage_Webapi_Block_Adminhtml_User_Edit_Tabs extends Mage_Backend_Block_Widget_Tabs
{
    /**
     * Internal constructor.
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('page_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('User Information'));
    }

    /**
     * Before to HTML.
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _beforeToHtml()
    {
        /** @var Mage_Webapi_Block_Adminhtml_User_Edit_Tab_Main $mainTab */
        $mainTab = $this->getLayout()->getBlock('webapi.user.edit.tab.main');
        $mainTab->setApiUser($this->getApiUser());
        $this->addTab('main_section', array(
            'label' => $this->__('User Info'),
            'title' => $this->__('User Info'),
            'content' => $mainTab->toHtml(),
            'active' => true
        ));

        $rolesGrid = $this->getLayout()->getBlock('webapi.user.edit.tab.roles.grid');
        $this->addTab('roles_section', array(
            'label' => $this->__('User Role'),
            'title' => $this->__('User Role'),
            'content' => $rolesGrid->toHtml(),
        ));
        return parent::_beforeToHtml();
    }
}
