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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\User\Controller\Adminhtml\User;

/**
 * Test class for \Magento\User\Controller\Adminhtml\User\Role.
 *
 * @magentoAppArea adminhtml
 */
class RoleTest extends \Magento\Backend\Utility\Controller
{
    public function testEditRoleAction()
    {
        $roleAdmin = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Authorization\Model\Role');
        $roleAdmin->load(\Magento\TestFramework\Bootstrap::ADMIN_ROLE_NAME, 'role_name');

        $this->getRequest()->setParam('rid', $roleAdmin->getId());

        $this->dispatch('backend/admin/user_role/editrole');

        $this->assertContains('Role Information', $this->getResponse()->getBody());
        $this->assertContains($roleAdmin->getRoleName(), $this->getResponse()->getBody());
    }

    /**
     * @covers \Magento\User\Controller\Adminhtml\User\Role\Editrolegrid::execute
     */
    public function testEditrolegridAction()
    {
        $this->getRequest()->setParam('ajax', true)->setParam('isAjax', true);
        $this->dispatch('backend/admin/user_role/editrolegrid');
        $expected = '%a<table %a id="roleUserGrid_table">%a';
        $this->assertStringMatchesFormat($expected, $this->getResponse()->getBody());
    }

    /**
     * @covers \Magento\User\Controller\Adminhtml\User\Role\RoleGrid::execute
     */
    public function testRoleGridAction()
    {
        $this->getRequest()->setParam('ajax', true)->setParam('isAjax', true)->setParam('user_id', 1);
        $this->dispatch('backend/admin/user_role/roleGrid');
        $expected = '%a<table %a id="roleGrid_table">%a';
        $this->assertStringMatchesFormat($expected, $this->getResponse()->getBody());
    }
}
