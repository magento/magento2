<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Model\Auth;

use Magento\TestFramework\Bootstrap as TestHelper;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class SessionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Model\Auth
     */
    private $auth;

    /**
     * @var Session
     */
    private $authSession;

    /**
     * @var SessionFactory
     */
    private $authSessionFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->objectManager->get(\Magento\Framework\Config\ScopeInterface::class)
            ->setCurrentScope(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        $this->auth = $this->objectManager->create(\Magento\Backend\Model\Auth::class);
        $this->authSession = $this->objectManager->create(Session::class);
        $this->authSessionFactory = $this->objectManager->get(SessionFactory::class);
        $this->auth->setAuthStorage($this->authSession);
        $this->auth->logout();
    }

    protected function tearDown()
    {
        $this->auth = null;
        $this->objectManager->get(\Magento\Framework\Config\ScopeInterface::class)->setCurrentScope(null);
    }

    /**
     * @dataProvider loginDataProvider
     */
    public function testIsLoggedIn($loggedIn)
    {
        if ($loggedIn) {
            $this->auth->login(
                TestHelper::ADMIN_NAME,
                TestHelper::ADMIN_PASSWORD
            );
        }
        $this->assertEquals($loggedIn, $this->authSession->isLoggedIn());
    }

    public function loginDataProvider()
    {
        return [[false], [true]];
    }

    /**
     * Check that persisting user data is working.
     */
    public function testStorage()
    {
        $this->auth->login(TestHelper::ADMIN_NAME, TestHelper::ADMIN_PASSWORD);
        $user = $this->authSession->getUser();
        $acl = $this->authSession->getAcl();
        /** @var Session $session */
        $session = $this->authSessionFactory->create();
        $persistedUser = $session->getUser();
        $persistedAcl = $session->getAcl();

        $this->assertEquals($user->getData(), $persistedUser->getData());
        $this->assertEquals($user->getAclRole(), $persistedUser->getAclRole());
        $this->assertEquals($acl->getRoles(), $persistedAcl->getRoles());
        $this->assertEquals($acl->getResources(), $persistedAcl->getResources());
    }

    /**
     * Check that session manager can work with user storage in the old way.
     */
    public function testInnerStorage(): void
    {
        /** @var \Magento\Framework\Session\StorageInterface $innerStorage */
        $innerStorage = Bootstrap::getObjectManager()->get(\Magento\Framework\Session\StorageInterface::class);
        $this->authSession = $this->authSessionFactory->create(['storage' => $innerStorage]);
        $this->auth->login(TestHelper::ADMIN_NAME, TestHelper::ADMIN_PASSWORD);
        $user = $this->auth->getAuthStorage()->getUser();
        $acl = $this->auth->getAuthStorage()->getAcl();
        $this->assertNotEmpty($user);
        $this->assertNotEmpty($acl);
        $this->auth->logout();
        $this->assertEmpty($this->auth->getAuthStorage()->getUser());
        $this->assertEmpty($this->auth->getAuthStorage()->getAcl());
        $this->authSession->setUser($user);
        $this->authSession->setAcl($acl);
        $this->assertTrue($user === $this->authSession->getUser());
        $this->assertTrue($acl === $this->authSession->getAcl());
        $this->authSession->destroy();
        $innerStorage->setUser($user);
        $innerStorage->setAcl($acl);
        $this->assertTrue($user === $this->authSession->getUser());
        $this->assertTrue($acl === $this->authSession->getAcl());
        /** @var Session $newSession */
        $newSession = $this->authSessionFactory->create(['storage' => $innerStorage]);
        $this->assertTrue($newSession->hasUser());
        $this->assertTrue($newSession->hasAcl());
        $this->assertEquals($user->getId(), $newSession->getUser()->getId());
    }
}
