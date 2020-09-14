<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Observer;

use DateTime;
use DateTimeZone;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Persistent\Helper\Session as PersistentSessionHelper;
use Magento\Persistent\Model\Session;
use Magento\Persistent\Model\SessionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for synchronize persistent session on login observer
 *
 * @see \Magento\Persistent\Observer\SynchronizePersistentOnLoginObserver
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 * @magentoDataFixture Magento/Customer/_files/customer.php
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SynchronizePersistentOnLoginObserverTest extends TestCase
{
    /**
     * @var SynchronizePersistentOnLoginObserver
     */
    private $model;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var PersistentSessionHelper
     */
    private $persistentSessionHelper;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var SessionFactory
     */
    private $persistentSessionFactory;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->persistentSessionHelper = $this->objectManager->get(PersistentSessionHelper::class);
        $this->model = $this->objectManager->get(SynchronizePersistentOnLoginObserver::class);
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $this->persistentSessionFactory = $this->objectManager->get(SessionFactory::class);
        $this->cookieManager = $this->objectManager->get(CookieManagerInterface::class);
        $this->customerSession = $this->objectManager->get(CustomerSession::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->persistentSessionHelper->setRememberMeChecked(null);
        $this->customerSession->logout();

        parent::tearDown();
    }

    /**
     * Test that persistent session is created on customer login
     *
     * @return void
     */
    public function testSynchronizePersistentOnLogin(): void
    {
        $customer = $this->customerRepository->get('customer@example.com');
        $sessionModel = $this->persistentSessionFactory->create();
        $sessionModel->loadByCustomerId($customer->getId());
        $this->assertNull($sessionModel->getCustomerId());
        $this->persistentSessionHelper->setRememberMeChecked(true);
        $this->customerSession->loginById($customer->getId());
        $sessionModel = $this->persistentSessionFactory->create();
        $sessionModel->loadByCustomerId($customer->getId());
        $this->assertEquals($customer->getId(), $sessionModel->getCustomerId());
    }

    /**
     * Test that expired persistent session is renewed on customer login
     *
     * @return void
     */
    public function testExpiredPersistentSessionShouldBeRenewedOnLogin(): void
    {
        $customer = $this->customerRepository->get('customer@example.com');
        $lastUpdatedAt = (new DateTime('-1day'))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
        $sessionModel = $this->persistentSessionFactory->create();
        $sessionModel->setCustomerId($customer->getId());
        $sessionModel->setUpdatedAt($lastUpdatedAt);
        $sessionModel->save();
        $this->persistentSessionHelper->setRememberMeChecked(true);
        $this->customerSession->loginById($customer->getId());
        $sessionModel = $this->persistentSessionFactory->create();
        $sessionModel->loadByCustomerId($customer->getId());
        $this->assertGreaterThan($lastUpdatedAt, $sessionModel->getUpdatedAt());
    }

    /**
     * @magentoDataFixture Magento/Persistent/_files/persistent_with_customer_quote_and_cookie.php
     * @magentoConfigFixture current_store persistent/options/enabled 0
     *
     * @return void
     */
    public function testDisabledPersistentSession(): void
    {
        $customer = $this->customerRepository->get('customer@example.com');
        $this->customerSession->loginById($customer->getId());
        $this->assertNull($this->cookieManager->getCookie(Session::COOKIE_NAME));
    }

    /**
     * @magentoDataFixture Magento/Persistent/_files/persistent_with_customer_quote_and_cookie.php
     * @magentoConfigFixture current_store persistent/options/enabled 1
     * @magentoConfigFixture current_store persistent/options/lifetime 0
     *
     * @return void
     */
    public function testDisabledPersistentSessionLifetime(): void
    {
        $customer = $this->customerRepository->get('customer@example.com');
        $this->customerSession->loginById($customer->getId());
        $session = $this->persistentSessionFactory->create()->setLoadExpired()->loadByCustomerId($customer->getId());
        $this->assertNull($session->getId());
        $this->assertNull($this->cookieManager->getCookie(Session::COOKIE_NAME));
    }
}
