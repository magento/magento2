<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml;

/**
 * Test class for \Magento\User\Controller\Adminhtml\Auth
 *
 * @magentoAppArea adminhtml
 */
class AuthTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Test form existence
     * @covers \Magento\User\Controller\Adminhtml\Auth\Forgotpassword::execute
     */
    public function testFormForgotpasswordAction()
    {
        $this->dispatch('backend/admin/auth/forgotpassword');
        $expected = 'Password Help';
        $this->assertContains($expected, $this->getResponse()->getBody());
    }

    /**
     * Test redirection to startup page after success password recovering posting
     *
     * @covers \Magento\User\Controller\Adminhtml\Auth\Forgotpassword::execute
     * @magentoDbIsolation enabled
     */
    public function testForgotpasswordAction()
    {
        $this->getRequest()->setPostValue('email', 'test@test.com');
        $this->dispatch('backend/admin/auth/forgotpassword');
        $this->assertRedirect(
            $this->equalTo(
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    \Magento\Backend\Helper\Data::class
                )->getHomePageUrl()
            )
        );
    }

    /**
     * Test email sending for forgotPassword action
     *
     * @magentoAdminConfigFixture admin/emails/forgot_email_template admin_emails_forgot_email_template
     * @magentoAdminConfigFixture admin/emails/forgot_email_identity general
     * @magentoDataFixture Magento/User/_files/user_with_role.php
     */
    public function testEmailSendForgotPasswordAction()
    {
        $transportBuilderMock = $this->prepareEmailMock(
            1,
            'admin_emails_forgot_email_template',
            'general'
        );
        $this->addMockToClass($transportBuilderMock, \Magento\User\Model\User::class);

        $this->getRequest()->setPostValue('email', 'adminUser@example.com');
        $this->dispatch('backend/admin/auth/forgotpassword');
        $this->assertRedirect(
            $this->equalTo(
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    \Magento\Backend\Helper\Data::class
                )->getHomePageUrl()
            )
        );
    }

    /**
     * Test reset password action
     *
     * @covers \Magento\User\Controller\Adminhtml\Auth\ResetPassword::execute
     * @covers \Magento\User\Controller\Adminhtml\Auth\ResetPassword::_validateResetPasswordLinkToken
     * @magentoDataFixture Magento/User/_files/dummy_user.php
     */
    public function testResetPasswordAction()
    {
        /** @var $user \Magento\User\Model\User */
        $user = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\User\Model\User::class
        )->loadByUsername(
            'dummy_username'
        );
        $this->assertNotEmpty($user->getId(), 'Broken fixture');
        $resetPasswordToken = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\User\Helper\Data::class
        )->generateResetPasswordLinkToken();
        $user->changeResetPasswordLinkToken($resetPasswordToken);
        $user->save();

        $this->getRequest()->setQueryValue('token', $resetPasswordToken)->setQueryValue('id', $user->getId());
        $this->dispatch('backend/admin/auth/resetpassword');

        $this->assertEquals('adminhtml', $this->getRequest()->getRouteName());
        $this->assertEquals('auth', $this->getRequest()->getControllerName());
        $this->assertEquals('resetpassword', $this->getRequest()->getActionName());
        $this->assertTrue((bool)strpos($this->getResponse()->getBody(), $resetPasswordToken));
    }

    /**
     * @covers \Magento\User\Controller\Adminhtml\Auth\ResetPassword::execute
     * @covers \Magento\User\Controller\Adminhtml\Auth\ResetPassword::_validateResetPasswordLinkToken
     */
    public function testResetPasswordActionWithDummyToken()
    {
        $this->getRequest()->setQueryValue('token', 'dummy')->setQueryValue('id', 1);
        $this->dispatch('backend/admin/auth/resetpassword');
        $this->assertSessionMessages(
            $this->equalTo(['Your password reset link has expired.']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
        $this->assertRedirect();
    }

    /**
     * @dataProvider resetPasswordDataProvider
     * @covers \Magento\User\Controller\Adminhtml\Auth\ResetPasswordPost::execute
     * @covers \Magento\User\Controller\Adminhtml\Auth\ResetPasswordPost::_validateResetPasswordLinkToken
     * @magentoDataFixture Magento/User/_files/dummy_user.php
     */
    public function testResetPasswordPostAction($password, $passwordConfirmation, $isPasswordChanged)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var $user \Magento\User\Model\User */
        $user = $objectManager->create(\Magento\User\Model\User::class);
        $user->loadByUsername('dummy_username');
        $this->assertNotEmpty($user->getId(), 'Broken fixture');

        /** @var \Magento\User\Helper\Data $helper */
        $helper = $objectManager->get(\Magento\User\Helper\Data::class);

        $resetPasswordToken = $helper->generateResetPasswordLinkToken();
        $user->changeResetPasswordLinkToken($resetPasswordToken);
        $user->save();
        $oldPassword = $user->getPassword();

        $this->getRequest()->setQueryValue(
            'token',
            $resetPasswordToken
        )->setQueryValue(
            'id',
            $user->getId()
        )->setPostValue(
            'password',
            $password
        )->setPostValue(
            'confirmation',
            $passwordConfirmation
        );

        $this->dispatch('backend/admin/auth/resetpasswordpost');

        /** @var \Magento\Backend\Helper\Data $backendHelper */
        $backendHelper = $objectManager->get(\Magento\Backend\Helper\Data::class);
        if ($isPasswordChanged) {
            $this->assertRedirect($this->equalTo($backendHelper->getHomePageUrl()));
        } else {
            $this->assertRedirect(
                $this->stringContains('backend/admin/auth/resetpassword')
            );
        }

        /** @var $user \Magento\User\Model\User */
        $user = $objectManager->create(\Magento\User\Model\User::class);
        $user->loadByUsername('dummy_username');

        if ($isPasswordChanged) {
            /** @var \Magento\Framework\Encryption\EncryptorInterface $encryptor */
            $encryptor = $objectManager->get(\Magento\Framework\Encryption\EncryptorInterface::class);
            $this->assertTrue($encryptor->validateHash($password, $user->getPassword()));
        } else {
            $this->assertEquals($oldPassword, $user->getPassword());
        }
    }

    public function resetPasswordDataProvider()
    {
        $password = uniqid('123q');
        return [
            [$password, $password, true],
            [$password, '', false],
            [$password, $password . '123', false],
            ['', '', false],
            ['', $password, false]
        ];
    }

    /**
     * @covers \Magento\User\Controller\Adminhtml\Auth\ResetPasswordPost::execute
     * @covers \Magento\User\Controller\Adminhtml\Auth\ResetPasswordPost::_validateResetPasswordLinkToken
     * @magentoDataFixture Magento/User/_files/dummy_user.php
     */
    public function testResetPasswordPostActionWithDummyToken()
    {
        $this->getRequest()->setQueryValue('token', 'dummy')->setQueryValue('id', 1);
        $this->dispatch('backend/admin/auth/resetpasswordpost');
        $this->assertSessionMessages(
            $this->equalTo(['Your password reset link has expired.']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Backend\Helper\Data $backendHelper */
        $backendHelper = $objectManager->get(\Magento\Backend\Helper\Data::class);

        $this->assertRedirect($this->equalTo($backendHelper->getHomePageUrl()));
    }

    /**
     * @covers \Magento\User\Controller\Adminhtml\Auth\ResetPasswordPost::execute
     * @covers \Magento\User\Controller\Adminhtml\Auth\ResetPasswordPost::_validateResetPasswordLinkToken
     * @magentoDataFixture Magento/User/_files/dummy_user.php
     */
    public function testResetPasswordPostActionWithInvalidPassword()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $user = $objectManager->create(\Magento\User\Model\User::class);
        $user->loadByUsername('dummy_username');
        $resetPasswordToken = null;
        if ($user->getId()) {
            /** @var \Magento\User\Helper\Data $userHelper */
            $userHelper = $objectManager->get(\Magento\User\Helper\Data::class);

            $resetPasswordToken = $userHelper->generateResetPasswordLinkToken();
            $user->changeResetPasswordLinkToken($resetPasswordToken);
            $user->save();
        }

        $newDummyPassword = 'new_dummy_password2';

        $this->getRequest()->setQueryValue(
            'token',
            $resetPasswordToken
        )->setQueryValue(
            'id',
            $user->getId()
        )->setPostValue(
            'password',
            $newDummyPassword
        )->setPostValue(
            'confirmation',
            'invalid'
        );

        $this->dispatch('backend/admin/auth/resetpasswordpost');

        $this->assertSessionMessages(
            $this->equalTo(['Your password confirmation must match your password.']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
        $this->assertRedirect();
    }

    /**
     * Prepare email mock to test emails
     *
     * @param int $occurrenceNumber
     * @param string $templateId
     * @param string $sender
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function prepareEmailMock($occurrenceNumber, $templateId, $sender)
    {
        $transportMock = $this->getMockBuilder(\Magento\Framework\Mail\TransportInterface::class)
            ->setMethods(['sendMessage'])
            ->getMockForAbstractClass();
        $transportMock->expects($this->exactly($occurrenceNumber))
            ->method('sendMessage');
        $transportBuilderMock = $this->getMockBuilder(\Magento\Framework\Mail\Template\TransportBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setTemplateModel',
                    'addTo',
                    'setFrom',
                    'setTemplateIdentifier',
                    'setTemplateVars',
                    'setTemplateOptions',
                    'getTransport'
                ]
            )
            ->getMock();
        $transportBuilderMock->method('setTemplateIdentifier')
            ->with($templateId)
            ->willReturnSelf();
        $transportBuilderMock->method('setTemplateModel')
            ->with(\Magento\Email\Model\BackendTemplate::class)
            ->willReturnSelf();
        $transportBuilderMock->method('setTemplateOptions')
            ->willReturnSelf();
        $transportBuilderMock->method('setTemplateVars')
            ->willReturnSelf();
        $transportBuilderMock->method('setFrom')
            ->with($sender)
            ->willReturnSelf();
        $transportBuilderMock->method('addTo')
            ->willReturnSelf();
        $transportBuilderMock->expects($this->exactly($occurrenceNumber))
            ->method('getTransport')
            ->willReturn($transportMock);

        return $transportBuilderMock;
    }

    /**
     * Add mocked object to environment
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $transportBuilderMock
     * @param string $originalClassName
     */
    protected function addMockToClass(
        \PHPUnit_Framework_MockObject_MockObject $transportBuilderMock,
        $originalClassName
    ) {
        $userMock = $this->_objectManager->create(
            $originalClassName,
            ['transportBuilder' => $transportBuilderMock]
        );
        $factoryMock = $this->getMockBuilder(\Magento\User\Model\UserFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'create'
                ]
            )
            ->getMock();
        $factoryMock->method('create')
            ->willReturn($userMock);
        $this->_objectManager->addSharedInstance(
            $factoryMock,
            \Magento\User\Model\UserFactory::class
        );
    }
}
