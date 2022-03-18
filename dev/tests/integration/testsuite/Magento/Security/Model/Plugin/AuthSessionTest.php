<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model\Plugin;

/**
 * @magentoAppIsolation enabled
 */
class AuthSessionTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Security\Model\ConfigInterface
     */
    protected $securityConfig;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->objectManager->get(\Magento\Framework\Config\ScopeInterface::class)
            ->setCurrentScope(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        $this->auth = $this->objectManager->create(\Magento\Backend\Model\Auth::class);
        $this->authSession = $this->objectManager->create(\Magento\Backend\Model\Auth\Session::class);
        $this->adminSessionInfo = $this->objectManager->create(\Magento\Security\Model\AdminSessionInfo::class);
        $this->auth->setAuthStorage($this->authSession);
        $this->adminSessionsManager = $this->objectManager->get(\Magento\Security\Model\AdminSessionsManager::class);
        $this->dateTime = $this->objectManager->create(\Magento\Framework\Stdlib\DateTime::class);
        $this->securityConfig = $this->objectManager->create(\Magento\Security\Model\ConfigInterface::class);
    }

    /**
     * Tear down
     */
    protected function tearDown(): void
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
     * session manager will not trigger new prolong if previous prolong was less than X sec ago
     * X - is calculated based on current admin session lifetime
     *
     * @see \Magento\Security\Model\AdminSessionsManager::lastProlongIsOldEnough
     * @magentoDbIsolation enabled
     */
    public function testConsecutiveProcessProlong()
    {
        $this->auth->login(
            \Magento\TestFramework\Bootstrap::ADMIN_NAME,
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );
        $adminSessionInfoId = $this->authSession->getAdminSessionInfoId();
        $prolongsDiff = log($this->securityConfig->getAdminSessionLifetime()) - 2; // X from comment above
        $dateInPast = $this->dateTime->formatDate($this->authSession->getUpdatedAt() - $prolongsDiff);
        $this->adminSessionsManager->getCurrentSession()
            ->setData(
                'updated_at',
                $dateInPast
            )
            ->save();
        $this->adminSessionInfo->load($adminSessionInfoId, 'id');
        $oldUpdatedAt = $this->adminSessionInfo->getUpdatedAt();
        $this->authSession->prolong();
        $this->adminSessionInfo->load($adminSessionInfoId, 'id');
        $updatedAt = $this->adminSessionInfo->getUpdatedAt();

        $this->assertSame(strtotime($oldUpdatedAt), strtotime($updatedAt));
    }
    /**
     * Test of prolong user action
     * session manager will trigger new prolong if previous prolong was more than X sec ago
     * X - is calculated based on current admin session lifetime
     *
     * @see \Magento\Security\Model\AdminSessionsManager::lastProlongIsOldEnough
     * @magentoDbIsolation enabled
     */
    public function testProcessProlong()
    {
        $this->auth->login(
            \Magento\TestFramework\Bootstrap::ADMIN_NAME,
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );
        $adminSessionInfoId = $this->authSession->getAdminSessionInfoId();
        $prolongsDiff = 4 * log($this->securityConfig->getAdminSessionLifetime()) + 2; // X from comment above
        $dateInPast = $this->dateTime->formatDate($this->authSession->getUpdatedAt() - $prolongsDiff);
        $this->adminSessionsManager->getCurrentSession()
            ->setData(
                'updated_at',
                $dateInPast
            )
            ->save();
        $this->adminSessionInfo->load($adminSessionInfoId, 'id');
        $oldUpdatedAt = $this->adminSessionInfo->getUpdatedAt();
        $this->authSession->prolong();
        $this->adminSessionInfo->load($adminSessionInfoId, 'id');
        $updatedAt = $this->adminSessionInfo->getUpdatedAt();

        $this->assertGreaterThan(strtotime($oldUpdatedAt), strtotime($updatedAt));
    }
}
