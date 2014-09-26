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
namespace Magento\Backend\Controller\Adminhtml\System;

use Magento\TestFramework\Bootstrap;

/**
 * @magentoAppArea adminhtml
 */
class AccountTest extends \Magento\Backend\Utility\Controller
{
    /**
     * @dataProvider saveDataProvider
     * @magentoDbIsolation enabled
     */
    public function testSaveAction($password, $passwordConfirmation, $isPasswordChanged)
    {
        $userId = $this->_session->getUser()->getId();
        /** @var $user \Magento\User\Model\User */
        $user = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\User\Model\User'
        )->load(
            $userId
        );
        $oldPassword = $user->getPassword();

        $request = $this->getRequest();
        $request->setParam(
            'username',
            $user->getUsername()
        )->setParam(
            'email',
            $user->getEmail()
        )->setParam(
            'firstname',
            $user->getFirstname()
        )->setParam(
            'lastname',
            $user->getLastname()
        )->setParam(
            'password',
            $password
        )->setParam(
            'password_confirmation',
            $passwordConfirmation
        )->setParam(
            \Magento\Backend\Block\System\Account\Edit\Form::IDENTITY_VERIFICATION_PASSWORD_FIELD,
            Bootstrap::ADMIN_PASSWORD
        );
        $this->dispatch('backend/admin/system_account/save');

        /** @var $user \Magento\User\Model\User */
        $user = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\User\Model\User'
        )->load(
            $userId
        );

        if ($isPasswordChanged) {
            $this->assertNotEquals($oldPassword, $user->getPassword());
            $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
            /** @var $encryptor \Magento\Framework\Encryption\EncryptorInterface */
            $encryptor = $objectManager->get('Magento\Framework\Encryption\EncryptorInterface');
            $this->assertTrue($encryptor->validateHash($password, $user->getPassword()));

        } else {
            $this->assertEquals($oldPassword, $user->getPassword());
        }
    }

    public function saveDataProvider()
    {
        $password = uniqid('123q');
        return array(
            array($password, $password, true),
            array($password, '', false),
            array($password, $password . '123', false),
            array('', '', false),
            array('', $password, false)
        );
    }
}
