<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml;

use Magento\Backend\Model\Auth;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test class for \Magento\Backend\Controller\Adminhtml\Auth
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class AuthTest extends AbstractController
{
    /**
     * @var Auth
     */
    private $auth;

    /**
     * @var Auth\Session
     */
    private $session;

    protected function tearDown()
    {
        $this->session = null;
        $this->auth = null;
        parent::tearDown();
    }

    /**
     * Performs user login
     */
    protected function login()
    {
        Bootstrap::getObjectManager()->get(UrlInterface::class)->turnOffSecretKey();

        $this->auth = Bootstrap::getObjectManager()->get(Auth::class);
        $this->auth->login(
            \Magento\TestFramework\Bootstrap::ADMIN_NAME,
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );
        $this->session = $this->auth->getAuthStorage();
    }

    /**
     * Performs user logout
     */
    private function logout()
    {
        $this->auth->logout();
        Bootstrap::getObjectManager()->get(UrlInterface::class)->turnOnSecretKey();
    }

    /**
     * Check not logged state
     * @covers \Magento\Backend\Controller\Adminhtml\Auth\Login::execute
     */
    public function testNotLoggedLoginAction()
    {
        $this->dispatch('backend/admin/auth/login');
        /** @var $backendUrlModel UrlInterface */
        $backendUrlModel = Bootstrap::getObjectManager()->get(UrlInterface::class);
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
        $this->login();

        $this->dispatch('backend/admin/auth/login');
        /** @var $backendUrlModel UrlInterface */
        $backendUrlModel = Bootstrap::getObjectManager()->get(UrlInterface::class);
        $url = $backendUrlModel->getStartupPageUrl();
        $expected = $backendUrlModel->getUrl($url);
        $this->assertRedirect($this->stringStartsWith($expected));

        $this->logout();
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testNotLoggedLoginActionWithRedirect()
    {
        /** @var FormKey $formKey */
        $formKey = $this->_objectManager->get(FormKey::class);
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

        $response = Bootstrap::getObjectManager()->get(ResponseInterface::class);
        $code = $response->getHttpResponseCode();

        $this->assertTrue($code >= 300 && $code < 400, 'Incorrect response code');
        $this->assertTrue(Bootstrap::getObjectManager()->get(Auth::class)->isLoggedIn());
    }

    /**
     * @covers \Magento\Backend\Controller\Adminhtml\Auth\Logout::execute
     * @magentoDbIsolation enabled
     */
    public function testLogoutAction()
    {
        $this->login();
        $this->dispatch('backend/admin/auth/logout');
        $this->assertRedirect(
            $this->equalTo(
                Bootstrap::getObjectManager()->get(\Magento\Backend\Helper\Data::class)->getHomePageUrl()
            )
        );
        $this->assertFalse($this->session->isLoggedIn(), 'User is not logged out.');
    }

    /**
     * @covers \Magento\Backend\Controller\Adminhtml\Auth\DeniedJson::execute
     * @covers \Magento\Backend\Controller\Adminhtml\Auth\DeniedJson::_getDeniedJson
     * @magentoDbIsolation enabled
     */
    public function testDeniedJsonAction()
    {
        $this->login();
        $this->dispatch('backend/admin/auth/deniedJson');
        $data = [
            'ajaxExpired' => 1,
            'ajaxRedirect' => Bootstrap::getObjectManager()->get(
                \Magento\Backend\Helper\Data::class
            )->getHomePageUrl(),
        ];
        $expected = json_encode($data);
        $this->assertEquals($expected, $this->getResponse()->getBody());
        $this->logout();
    }

    /**
     * @covers \Magento\Backend\Controller\Adminhtml\Auth\DeniedIframe::execute
     * @covers \Magento\Backend\Controller\Adminhtml\Auth\DeniedIframe::_getDeniedIframe
     * @magentoDbIsolation enabled
     */
    public function testDeniedIframeAction()
    {
        $this->login();
        $this->dispatch('backend/admin/auth/deniedIframe');
        $homeUrl = Bootstrap::getObjectManager()->get(
            \Magento\Backend\Helper\Data::class
        )->getHomePageUrl();
        $expected = '<script>parent.window.location =';
        $this->assertStringStartsWith($expected, $this->getResponse()->getBody());
        $this->assertContains($homeUrl, $this->getResponse()->getBody());
        $this->logout();
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
        /** @var FormKey $formKey */
        $formKey = $this->_objectManager->get(FormKey::class);
        $params['form_key'] = $formKey->getFormKey();
        $this->getRequest()->setPostValue($params);
        $this->dispatch('backend/admin/auth/login');
        $this->assertSessionMessages(
            $this->equalTo(
                [
                    'The account sign-in was incorrect or your account is disabled temporarily. '
                    . 'Please wait and try again later.'
                ]
            ),
            MessageInterface::TYPE_ERROR
        );
        $backendUrlModel = Bootstrap::getObjectManager()->get(UrlInterface::class);
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
