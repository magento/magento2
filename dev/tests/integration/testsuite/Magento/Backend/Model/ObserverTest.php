<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model;

/**
 * @magentoAppArea adminhtml
 */
class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Observer
     */
    protected $_model;

    protected function setUp()
    {
        parent::setUp();
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Backend\Model\Observer'
        );
    }

    public function testActionPreDispatchAdminNotLogged()
    {
        $this->markTestSkipped('Skipped because of authentication process moved into base controller.');

        $request = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\App\RequestInterface');
        $this->assertEmpty($request->getRouteName());
        $this->assertEmpty($request->getControllerName());
        $this->assertEmpty($request->getActionName());

        $observer = $this->_buildObserver();
        $this->_model->actionPreDispatchAdmin($observer);

        $this->assertEquals('adminhtml', $request->getRouteName());
        $this->assertEquals('auth', $request->getControllerName());
        $this->assertEquals('login', $request->getActionName());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testActionPreDispatchAdminLoggedRedirect()
    {
        $this->markTestSkipped('Skipped because of authentication process moved into base controller.');

        $observer = $this->_buildObserver();
        $this->_model->actionPreDispatchAdmin($observer);

        $response = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\App\ResponseInterface');
        $code = $response->getHttpResponseCode();
        $this->assertTrue($code >= 300 && $code < 400);

        $session = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Backend\Model\Auth\Session'
        );
        $this->assertTrue($session->isLoggedIn());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store admin/security/use_form_key 0
     */
    public function testActionPreDispatchAdminLoggedNoRedirect()
    {
        $this->markTestSkipped('Skipped because of authentication process moved into base controller.');

        $observer = $this->_buildObserver();
        $this->_model->actionPreDispatchAdmin($observer);

        $response = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\App\ResponseInterface');
        $code = $response->getHttpResponseCode();
        $this->assertFalse($code >= 300 && $code < 400);

        $session = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Backend\Model\Auth\Session'
        );
        $this->assertTrue($session->isLoggedIn());
    }

    /**
     * Builds a dummy observer for testing adminPreDispatch method
     *
     * @return \Magento\Framework\Object
     */
    protected function _buildObserver()
    {
        $request = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\App\RequestInterface');
        $request->setPost(
            'login',
            [
                'username' => \Magento\TestFramework\Bootstrap::ADMIN_NAME,
                'password' => \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
            ]
        );

        $controller = new \Magento\Framework\Object(['request' => $request]);
        $event = new \Magento\Framework\Object(['controller_action' => $controller]);
        $observer = new \Magento\Framework\Object(['event' => $event]);
        return $observer;
    }
}
