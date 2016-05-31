<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model\Plugin;

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
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

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
        $this->adminSessionsManager = $this->objectManager->create('Magento\Security\Model\AdminSessionsManager');
        $this->dateTime = $this->objectManager->create('Magento\Framework\Stdlib\DateTime');
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
     *
     * @magentoDbIsolation enabled
     */
    public function testProcessProlong()
    {
        $this->auth->login(
            \Magento\TestFramework\Bootstrap::ADMIN_NAME,
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );
        $sessionId = $this->authSession->getSessionId();
        $dateInPast = $this->dateTime->formatDate($this->authSession->getUpdatedAt() - 100);
        $this->adminSessionsManager->getCurrentSession()
            ->setData(
                'updated_at',
                $dateInPast
            )
            ->save();
        $this->adminSessionInfo->load($sessionId, 'session_id');
        $oldUpdatedAt = $this->adminSessionInfo->getUpdatedAt();
        $this->authSession->prolong();
        $this->adminSessionInfo->load($sessionId, 'session_id');
        $updatedAt = $this->adminSessionInfo->getUpdatedAt();
        $this->assertGreaterThan($oldUpdatedAt, $updatedAt);
    }
}
