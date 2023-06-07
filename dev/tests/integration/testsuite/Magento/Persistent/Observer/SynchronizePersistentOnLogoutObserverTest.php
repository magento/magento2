<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Observer;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\ObjectManagerInterface;
use Magento\Persistent\Model\SessionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for synchronize persistent on logout observer
 *
 * @see \Magento\Persistent\Observer\SynchronizePersistentOnLogoutObserver
 * @magentoDataFixture Magento/Customer/_files/customer.php
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 */
class SynchronizePersistentOnLogoutObserverTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var CustomerSession */
    private $customerSession;

    /** @var SessionFactory */
    private $sessionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerSession = $this->objectManager->get(CustomerSession::class);
        $this->sessionFactory = $this->objectManager->get(SessionFactory::class);
    }

    /**
     * @magentoConfigFixture current_store persistent/options/enabled 1
     * @magentoConfigFixture current_store persistent/options/logout_clear 1
     *
     * @return void
     */
    public function testSynchronizePersistentOnLogout(): void
    {
        $this->customerSession->loginById(1);
        $sessionModel = $this->sessionFactory->create();
        $sessionModel->loadByCookieKey();
        $this->assertEquals(1, $sessionModel->getCustomerId());
        $this->customerSession->logout();
        $sessionModel = $this->sessionFactory->create();
        $sessionModel->loadByCookieKey();
        $this->assertNull($sessionModel->getCustomerId());
    }

    /**
     * @magentoConfigFixture current_store persistent/options/enabled 1
     * @magentoConfigFixture current_store persistent/options/logout_clear 0
     *
     * @return void
     */
    public function testSynchronizePersistentOnLogoutDisabled(): void
    {
        $this->customerSession->loginById(1);
        $this->customerSession->logout();
        $sessionModel = $this->sessionFactory->create();
        $sessionModel->loadByCookieKey();
        $this->assertEquals(1, $sessionModel->getCustomerId());
    }
}
