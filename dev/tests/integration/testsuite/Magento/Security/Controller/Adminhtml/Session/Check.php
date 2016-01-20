<?php

namespace Magento\Security\Controller\Adminhtml\Session;

/**
 * Class CheckTest
 * @package Magento\Security\Controller\Adminhtml\Session
 */
class CheckTest extends \Magento\TestFramework\TestCase\AbstractBackendController
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

    public function testAdminSessionIsUpdated()
    {
        $this->_login();
        $securityConfigMock = $this->getMock('Magento\Security\Helper\SecurityConfig');



        return true;
    }



}