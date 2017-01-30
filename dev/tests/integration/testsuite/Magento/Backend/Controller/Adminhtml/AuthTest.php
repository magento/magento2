<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml;

use Magento\Framework\Message\MessageInterface;

/**
 * Test class for \Magento\Backend\Controller\Adminhtml\Auth
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class AuthTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_session;

    /**
     * @var \Magento\Backend\Model\Auth
     */
    protected $_auth;

    protected function tearDown()
    {
        $this->_session = null;
        $this->_auth = null;
        parent::tearDown();
    }

    /**
     * Performs user login
     */
    protected function _login()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Backend\Model\UrlInterface'
        )->turnOffSecretKey();

        $this->_auth = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Backend\Model\Auth');
        $this->_auth->login(
            \Magento\TestFramework\Bootstrap::ADMIN_NAME,
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );
        $this->_session = $this->_auth->getAuthStorage();
    }

    /**
     * Performs user logout
     */
    protected function _logout()
    {
        $this->_auth->logout();
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Backend\Model\UrlInterface'
        )->turnOnSecretKey();
    }

    /**
     * Check not logged state
     * @covers \Magento\Backend\Controller\Adminhtml\Auth\Login::execute
     */
    public function testNotLoggedLoginAction()
    {
        $this->dispatch('backend/admin/auth/login');
        /** @var $backendUrlModel \Magento\Backend\Model\UrlInterface */
        $backendUrlModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Backend\Model\UrlInterface'
        );
        $backendUrlModel->turnOffSecretKey();
        $url = $backendUrlModel->getUrl('admin');
        $this->assertRedirect($this->stringStartsWith($url));
    }

    /**
     * Check logged state
     * @covers \Magento\Backend\Controller\Adminhtml\Auth\Login::execute
     * @magentoDbIsolation enabled
     */
    public function testLoggedLoginAction()
    {
        $this->_login();

        $this->dispatch('backend/admin/auth/login');
        /** @var $backendUrlModel \Magento\Backend\Model\UrlInterface */
        $backendUrlModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Backend\Model\UrlInterface'
        );
        $url = $backendUrlModel->getStartupPageUrl();
        $expected = $backendUrlModel->getUrl($url);
        $this->assertRedirect($this->stringStartsWith($expected));

        $this->_logout();
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testNotLoggedLoginActionWithRedirect()
    {
        /** @var \Magento\Framework\Data\Form\FormKey $formKey */
        $formKey = $this->_objectManager->get('Magento\Framework\Data\Form\FormKey');
        $this->getRequest()->setPostValue(
            [
                'login' => [
                    'username' => \Magento\TestFramework\Bootstrap::ADMIN_NAME,
                    'password' => \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD,
                ],
                'form_key' => $formKey->getFormKey(),
            ]
        );

        $this->dispatch('backend/admin/index/index');

        $response = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\App\ResponseInterface');
        $code = $response->getHttpResponseCode();
        $this->assertTrue($code >= 300 && $code < 400, 'Incorrect response code');

        $this->assertTrue(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Backend\Model\Auth'
            )->isLoggedIn()
        );
    }

    /**
     * @covers \Magento\Backend\Controller\Adminhtml\Auth\Logout::execute
     * @magentoDbIsolation enabled
     */
    public function testLogoutAction()
    {
        $this->_login();
        $this->dispatch('backend/admin/auth/logout');
        $this->assertRedirect(
            $this->equalTo(
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    'Magento\Backend\Helper\Data'
                )->getHomePageUrl()
            )
        );
        $this->assertFalse($this->_session->isLoggedIn(), 'User is not logged out.');
    }

    /**
     * @covers \Magento\Backend\Controller\Adminhtml\Auth\DeniedJson::execute
     * @covers \Magento\Backend\Controller\Adminhtml\Auth\DeniedJson::_getDeniedJson
     * @magentoDbIsolation enabled
     */
    public function testDeniedJsonAction()
    {
        $this->_login();
        $this->dispatch('backend/admin/auth/deniedJson');
        $data = [
            'ajaxExpired' => 1,
            'ajaxRedirect' => \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Backend\Helper\Data'
            )->getHomePageUrl(),
        ];
        $expected = json_encode($data);
        $this->assertEquals($expected, $this->getResponse()->getBody());
        $this->_logout();
    }

    /**
     * @covers \Magento\Backend\Controller\Adminhtml\Auth\DeniedIframe::execute
     * @covers \Magento\Backend\Controller\Adminhtml\Auth\DeniedIframe::_getDeniedIframe
     * @magentoDbIsolation enabled
     */
    public function testDeniedIframeAction()
    {
        $this->_login();
        $this->dispatch('backend/admin/auth/deniedIframe');
        $homeUrl = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Backend\Helper\Data'
        )->getHomePageUrl();
        $expected = '<script>parent.window.location =';
        $this->assertStringStartsWith($expected, $this->getResponse()->getBody());
        $this->assertContains($homeUrl, $this->getResponse()->getBody());
        $this->_logout();
    }

    /**
     * Test user logging process when user not assigned to any role
     * @dataProvider incorrectLoginDataProvider
     * @magentoDbIsolation enabled
     *
     * @param $params
     */
    public function testIncorrectLogin($params)
    {
        /** @var \Magento\Framework\Data\Form\FormKey $formKey */
        $formKey = $this->_objectManager->get('Magento\Framework\Data\Form\FormKey');
        $params['form_key'] = $formKey->getFormKey();
        $this->getRequest()->setPostValue($params);
        $this->dispatch('backend/admin/auth/login');
        $this->assertSessionMessages(
            $this->equalTo(['You did not sign in correctly or your account is temporarily disabled.']),
            MessageInterface::TYPE_ERROR
        );
        $backendUrlModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Backend\Model\UrlInterface'
        );
        $backendUrlModel->turnOffSecretKey();
        $url = $backendUrlModel->getUrl('admin');
        $this->assertRedirect($this->stringStartsWith($url));
    }

    public function incorrectLoginDataProvider()
    {
        return [
            'login dummy user' => [
                [
                    'login' => [
                        'username' => 'test1',
                        'password' => \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD,
                    ],
                ],
            ],
            'login without role' => [
                [
                    'login' => [
                        'username' => 'test2',
                        'password' => \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD,
                    ],
                ],
            ],
            'login not active user' => [
                [
                    'login' => [
                        'username' => 'test3',
                        'password' => \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD,
                    ],
                ],
            ]
        ];
    }
}
