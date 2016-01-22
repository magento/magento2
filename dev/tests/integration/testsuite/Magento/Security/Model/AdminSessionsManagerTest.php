<?php

namespace Magento\Security\Model;

/**
 * Class AdminSessionsManagerTest
 * @package Magento\Security\Model
 */
class AdminSessionsManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Auth
     */
    protected $auth;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $modelSession;

    /**
     * @var \Magento\Security\Model\AdminSessionInfo
     */
    protected $modelSessionInfo;

    protected function setUp()
    {
        parent::setUp();
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Config\ScopeInterface'
        )->setCurrentScope(
            \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
        );
        $this->auth = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Backend\Model\Auth'
        );
        $this->modelSession = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Backend\Model\Auth\Session'
        );
        $this->modelSessionInfo = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Security\Model\AdminSessionInfo'
        );

        $this->auth->setAuthStorage($this->modelSession);
    }

    protected function tearDown()
    {
        $this->auth = null;
        $this->modelSession  = null;
        $this->modelSessionInfo  = null;
        parent::tearDown();
    }

    /**
     * Checks is the admin session is created in database
     */
    public function testIsAdminSessionIsCreated()
    {
        $this->auth->login(
            \Magento\TestFramework\Bootstrap::ADMIN_NAME,
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );
        $sessionId = $this->modelSession->getSessionId();
        $this->modelSessionInfo->load($sessionId, 'session_id');
        $this->assertGreaterThanOrEqual(1, (int)$this->modelSessionInfo->getId());
        $this->auth->logout();
    }
}