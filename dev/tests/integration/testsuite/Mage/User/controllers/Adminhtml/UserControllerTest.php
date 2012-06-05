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
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_User
 */
class Mage_User_Adminhtml_UserControllerTest extends Mage_Adminhtml_Utility_Controller
{
    /**
     * @covers Mage_User_Adminhtml_UserController::indexAction
     */
    public function testIndexAction()
    {
        $this->dispatch('admin/user/index');
        $this->assertStringMatchesFormat('%a<div class="content-header">%aUsers%a', $this->getResponse()->getBody());
    }

    /**
     * @covers Mage_User_Adminhtml_UserController::rolesGridAction
     */
    public function testRoleGridAction()
    {
        $this->getRequest()
            ->setParam('ajax', true)
            ->setParam('isAjax', true);
        $this->dispatch('admin/user/roleGrid');
        $expected = '%a<table %a id="permissionsUserGrid_table">%a';
        $this->assertStringMatchesFormat($expected, $this->getResponse()->getBody());
    }

    /**
     * @covers Mage_User_Adminhtml_UserController::rolesGridAction
     */
    public function testRolesGridAction()
    {
        $this->getRequest()
            ->setParam('ajax', true)
            ->setParam('isAjax', true)
            ->setParam('user_id', 1);
        $this->dispatch('admin/user/rolesGrid');
        $expected = '%a<table %a id="permissionsUserRolesGrid_table">%a';
        $this->assertStringMatchesFormat($expected, $this->getResponse()->getBody());
    }

    /*
     * @covers Mage_User_Adminhtml_UserController::editAction
     */
    public function testEditAction()
    {
        $this->getRequest()->setParam('user_id', 1);
        $this->dispatch('admin/user/edit');
        $expected = '%a<h3 class="icon-head head-user">Edit User%a';
        $this->assertStringMatchesFormat($expected, $this->getResponse()->getBody());
    }
}
