<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model\Plugin;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppIsolation enabled
 */
class AuthSessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Auth
     */
    protected $auth;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magento\Security\Model\AdminSessionInfo
     */
    protected $adminSessionInfo;

    /**
     * @var \Magento\Security\Model\AdminSessionsManager
     */
    protected $adminSessionsManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Set up
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->objectManager->get('Magento\Framework\Config\ScopeInterface')
            ->setCurrentScope(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        $this->auth = $this->objectManager->create('Magento\Backend\Model\Auth');
        $this->authSession = $this->objectManager->create('Magento\Backend\Model\Auth\Session');
        $this->adminSessionInfo = $this->objectManager->create('Magento\Security\Model\AdminSessionInfo');
        $this->auth->setAuthStorage($this->authSession);
        $this->messageManager = $this->objectManager->get('Magento\Framework\Message\ManagerInterface');
        $this->adminSessionsManager = $this->objectManager->create('Magento\Security\Model\AdminSessionsManager');
    }

    /**
     * Tear down
     */
    protected function tearDown()
    {
        $this->auth = null;
        $this->authSession  = null;
        $this->adminSessionInfo  = null;
        $this->adminSessionsManager = null;
        $this->objectManager = null;
        parent::tearDown();
    }

    /**
     * Test of prolong user action
     */
    public function testProcessProlong()
    {
        $this->auth->login(
            \Magento\TestFramework\Bootstrap::ADMIN_NAME,
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );
        $sessionId = $this->authSession->getSessionId();
        $updatedAtOld = $this->authSession->getUpdatedAt();
        $this->authSession->setUpdatedAt(time() - 1000);

        $this->adminSessionInfo->load($sessionId, 'session_id');
        $updatedAtOld = $this->adminSessionInfo->getUpdatedAt();
        echo PHP_EOL;
        echo $updatedAtOld . PHP_EOL;
        $this->authSession->prolong();
        $updatedAt = $this->adminSessionInfo->getUpdatedAt();
        echo $updatedAt . PHP_EOL;

        //$this->assertEquals($this->adminSessionInfo->getUpdatedAt(), $updatedAt);
    }
}
