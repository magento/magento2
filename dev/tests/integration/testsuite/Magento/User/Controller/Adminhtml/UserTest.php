<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml;

use Magento\TestFramework\Bootstrap;

/**
 * @magentoAppArea adminhtml
 */
class UserTest extends \Magento\TestFramework\TestCase\AbstractBackendController
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
        $this->getRequest()->setPostValue('user_id', $userId);
        $this->dispatch('backend/admin/user/save');
        $this->assertSessionMessages(
            $this->equalTo(['This user no longer exists.']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
        $this->assertRedirect($this->stringContains('backend/admin/user/index/'));
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSaveActionMissingCurrentAdminPassword()
    {
        $fixture = uniqid();
        $this->getRequest()->setPostValue(
            [
                'username' => $fixture,
                'email' => "{$fixture}@example.com",
                'firstname' => 'First',
                'lastname' => 'Last',
                'password' => 'password_with_1_number',
                'password_confirmation' => 'password_with_1_number',
            ]
        );
        $this->dispatch('backend/admin/user/save');
        $this->assertSessionMessages($this->equalTo(['You have entered an invalid password for current user.']));
        $this->assertRedirect($this->stringContains('backend/admin/user/edit'));
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSaveAction()
    {
        $fixture = uniqid();
        $this->getRequest()->setPostValue(
            [
                'username' => $fixture,
                'email' => "{$fixture}@example.com",
                'firstname' => 'First',
                'lastname' => 'Last',
                'password' => 'password_with_1_number',
                'password_confirmation' => 'password_with_1_number',
                \Magento\User\Block\User\Edit\Tab\Main::CURRENT_USER_PASSWORD_FIELD => Bootstrap::ADMIN_PASSWORD,
            ]
        );
        $this->dispatch('backend/admin/user/save');
        $this->assertSessionMessages(
            $this->equalTo(['You saved the user.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringContains('backend/admin/user/index/'));
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/User/_files/user_with_role.php
     */
    public function testSaveActionDuplicateUser()
    {
        $this->getRequest()->setPostValue(
            [
                'username' => 'adminUser',
                'email' => 'adminUser@example.com',
                'firstname' => 'John',
                'lastname' => 'Doe',
                'password' => \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD,
                'password_confirmation' => \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD,
                \Magento\User\Block\User\Edit\Tab\Main::CURRENT_USER_PASSWORD_FIELD => Bootstrap::ADMIN_PASSWORD,
            ]
        );
        $this->dispatch('backend/admin/user/save/active_tab/main_section');
        $this->assertSessionMessages(
            $this->equalTo(['A user with the same user name or email already exists.']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
        $this->assertRedirect($this->stringContains('backend/admin/user/edit/'));
        $this->assertRedirect($this->matchesRegularExpression('/^((?!active_tab).)*$/'));
    }

    /**
     * @magentoDbIsolation enabled
     * @dataProvider resetPasswordDataProvider
     */
    public function testSaveActionPasswordChange($postData, $isPasswordCorrect)
    {
        $this->getRequest()->setPostValue($postData);
        $this->dispatch('backend/admin/user/save');

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $user \Magento\User\Model\User */
        $user = $objectManager->create('Magento\User\Model\User');
        $user->loadByUsername($postData['username']);
        if ($isPasswordCorrect) {
            $this->assertRedirect($this->stringContains('backend/admin/user/index'));
            $this->assertEquals($postData['username'], $user->getUsername());
            $this->assertEquals($postData['email'], $user->getEmail());
            $this->assertEquals($postData['firstname'], $user->getFirstname());
            $this->assertEquals($postData['lastname'], $user->getLastname());
            $encryptor = $objectManager->get('Magento\Framework\Encryption\EncryptorInterface');
            $this->assertTrue($encryptor->validateHash($postData['password'], $user->getPassword()));
        } else {
            $this->assertRedirect($this->stringContains('backend/admin/user/edit'));
            $this->assertEmpty($user->getData());
        }
    }

    public function resetPasswordDataProvider()
    {
        $password = uniqid('123q');
        $passwordPairs = [
            ['password' => $password, 'password_confirmation' => $password, 'is_correct' => true],
            ['password' => $password, 'password_confirmation' => '', 'is_correct' => false],
            ['password' => $password, 'password_confirmation' => $password . '123', 'is_correct' => false],
            ['password' => '', 'password_confirmation' => '', 'is_correct' => false],
            ['password' => '', 'password_confirmation' => $password, 'is_correct' => false],
        ];
        $data = [];
        foreach ($passwordPairs as $passwordPair) {
            $fixture = uniqid();
            $postData = [
                'username' => $fixture,
                'email' => "{$fixture}@example.com",
                'firstname' => 'First',
                'lastname' => 'Last',
                'password' => $passwordPair['password'],
                'password_confirmation' => $passwordPair['password_confirmation'],
                \Magento\User\Block\User\Edit\Tab\Main::CURRENT_USER_PASSWORD_FIELD => Bootstrap::ADMIN_PASSWORD,
            ];
            $data[] = [$postData, $passwordPair['is_correct']];
        }
        return $data;
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

    public function testValidateActionSuccess()
    {
        $data = [
            'username' => 'admin2',
            'firstname' => 'new firstname',
            'lastname' => 'new lastname',
            'email' => 'example@domain.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->getRequest()->setPostValue($data);
        $this->dispatch('backend/admin/user/validate');
        $body = $this->getResponse()->getBody();

        $this->assertEquals('{"error":0}', $body);
    }

    public function testValidateActionError()
    {
        $data = [
            'username' => 'admin2',
            'firstname' => 'new firstname',
            'lastname' => 'new lastname',
            'email' => 'example@domain.cim',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        /**
         * set customer data
         */
        $this->getRequest()->setPostValue($data);
        $this->dispatch('backend/admin/user/validate');
        $body = $this->getResponse()->getBody();

        $this->assertContains('{"error":1,"html_message":', $body);
        $this->assertContains("'domain.cim' is not a valid hostname for email address 'example@domain.cim'", $body);
    }
}
