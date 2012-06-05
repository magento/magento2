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
 * @category    Magento
 * @package     Magento_User
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_User_Adminhtml_User_RoleController.
 *
 * @group module:Mage_User
 */
class Mage_User_Adminhtml_User_RoleControllerTest extends Mage_Adminhtml_Utility_Controller
{
    public function testEditRoleAction()
    {
        $roleAdmin = new Mage_User_Model_Role();
        $roleAdmin->load(Magento_Test_Bootstrap::ADMIN_ROLE_NAME, 'role_name');

        $this->getRequest()->setParam('rid', $roleAdmin->getId());

        $this->dispatch('admin/user_role/editrole');

        $this->assertContains('Role Information', $this->getResponse()->getBody());
        $this->assertContains("Edit Role '" . $roleAdmin->getRoleName() . "'", $this->getResponse()->getBody());
    }

    /**
     * @covers Mage_User_Adminhtml_User_RoleController::editrolegridAction
     */
    public function testEditrolegridAction()
    {
        $this->getRequest()
            ->setParam('ajax', true)
            ->setParam('isAjax', true);
        $this->dispatch('admin/user_role/editrolegrid');
        $expected = '%a<table %a id="roleUserGrid_table">%a';
        $this->assertStringMatchesFormat($expected, $this->getResponse()->getBody());
    }

    /**
     * @covers Mage_User_Adminhtml_User_RoleController::roleGridAction
     */
    public function testRoleGridAction()
    {
        $this->getRequest()
            ->setParam('ajax', true)
            ->setParam('isAjax', true)
            ->setParam('user_id', 1);
        $this->dispatch('admin/user_role/roleGrid');
        $expected = '%a<table %a id="roleGrid_table">%a';
        $this->assertStringMatchesFormat($expected, $this->getResponse()->getBody());
    }
}
