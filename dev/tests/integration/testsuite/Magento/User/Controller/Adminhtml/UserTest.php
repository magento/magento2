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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\User\Controller\Adminhtml;

/**
 * @magentoAppArea adminhtml
 */
class UserTest extends \Magento\Backend\Utility\Controller
{
    public function testIndexAction()
    {
        $this->dispatch('backend/admin/user/index');
        $response = $this->getResponse()->getBody();
        $this->assertContains('Users', $response);
        $this->assertSelectCount('#permissionsUserGrid_table', 1, $response);
    }

    public function testSaveActionNoData()
    {
        $this->dispatch('backend/admin/user/save');
        $this->assertRedirect($this->stringContains('backend/admin/user/index/'));
    }

    /**
     * @magentoDataFixture Magento/User/_files/dummy_user.php
     */
    public function testSaveActionWrongId()
    {
        /** @var $user \Magento\User\Model\User */
        $user = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\User\Model\User'
        )->loadByUsername(
            'dummy_username'
        );
        $userId = $user->getId();
        $this->assertNotEmpty($userId, 'Broken fixture');
        $user->delete();
        $this->getRequest()->setPost('user_id', $userId);
        $this->dispatch('backend/admin/user/save');
        $this->assertSessionMessages(
            $this->equalTo(array('This user no longer exists.')),
            \Magento\Message\MessageInterface::TYPE_ERROR
        );
        $this->assertRedirect($this->stringContains('backend/admin/user/index/'));
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSaveAction()
    {
        $this->_createNew();
        $this->assertSessionMessages(
            $this->equalTo(array('You saved the user.')),
            \Magento\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringContains('backend/admin/user/index/'));
    }

    /**
     * Create new user through dispatching save action
     */
    private function _createNew()
    {
        $fixture = uniqid();
        $this->getRequest()->setPost(
            array(
                'username' => $fixture,
                'email' => "{$fixture}@example.com",
                'firstname' => 'First',
                'lastname' => 'Last',
                'password' => 'password_with_1_number',
                'password_confirmation' => 'password_with_1_number'
            )
        );
        $this->dispatch('backend/admin/user/save');
    }

    public function testRoleGridAction()
    {
        $this->getRequest()->setParam('ajax', true)->setParam('isAjax', true);
        $this->dispatch('backend/admin/user/roleGrid');
        $expected = '%a<table %a id="permissionsUserGrid_table">%a';
        $this->assertStringMatchesFormat($expected, $this->getResponse()->getBody());
    }

    /**
     * @depends testSaveAction
     */
    public function testRolesGridAction()
    {
        $this->getRequest()->setParam('ajax', true)->setParam('isAjax', true)->setParam('user_id', 1);
        $this->dispatch('backend/admin/user/rolesGrid');
        $expected = '%a<table %a id="permissionsUserRolesGrid_table">%a';
        $this->assertStringMatchesFormat($expected, $this->getResponse()->getBody());
    }

    /**
     * @depends testSaveAction
     */
    public function testEditAction()
    {
        $this->getRequest()->setParam('user_id', 1);
        $this->dispatch('backend/admin/user/edit');
        $response = $this->getResponse()->getBody();
        //check "User Information" header and fieldset
        $this->assertContains('data-ui-id="adminhtml-user-edit-tabs-title"', $response);
        $this->assertContains('User Information', $response);
        $this->assertSelectCount('#user_base_fieldset', 1, $response);
    }
}
