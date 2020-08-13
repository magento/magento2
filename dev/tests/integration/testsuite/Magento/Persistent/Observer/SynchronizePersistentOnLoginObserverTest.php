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
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\ObjectManagerInterface;
use Magento\Persistent\Model\Session;
use Magento\Persistent\Model\SessionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDataFixture Magento/Customer/_files/customer.php
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SynchronizePersistentOnLoginObserverTest extends TestCase
{
    /**
     * @var SynchronizePersistentOnLoginObserver
     */
    protected $_model;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Persistent\Helper\Session
     */
    protected $_persistentSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var CustomerInterface
     */
    private $customer;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->_objectManager = Bootstrap::getObjectManager();
        $this->_persistentSession = $this->_objectManager->get(\Magento\Persistent\Helper\Session::class);
        $this->_customerSession = $this->_objectManager->get(\Magento\Customer\Model\Session::class);
        $this->_model = $this->_objectManager->create(
            SynchronizePersistentOnLoginObserver::class,
            [
                'persistentSession' => $this->_persistentSession,
                'customerSession' => $this->_customerSession
            ]
        );
        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = $this->_objectManager->create(CustomerRepositoryInterface::class);
        $this->customer = $customerRepository->getById(1);
    }

    /**
     * Test that persistent session is created on customer login
     */
    public function testSynchronizePersistentOnLogin(): void
    {
        $sessionModel = $this->_objectManager->create(Session::class);
        $sessionModel->loadByCustomerId($this->customer->getId());
        $this->assertNull($sessionModel->getCustomerId());
        $event = new Event();
        $observer = new Observer(['event' => $event]);
        $event->setData('customer', $this->customer);
        $this->_persistentSession->setRememberMeChecked(true);
        $this->_model->execute($observer);
        // check that persistent session has been stored for Customer
        /** @var Session $sessionModel */
        $sessionModel = $this->_objectManager->create(Session::class);
        $sessionModel->loadByCustomerId($this->customer->getId());
        $this->assertEquals($this->customer->getId(), $sessionModel->getCustomerId());
    }

    /**
     * Test that expired persistent session is renewed on customer login
     */
    public function testExpiredPersistentSessionShouldBeRenewedOnLogin(): void
    {
        $lastUpdatedAt = (new DateTime('-1day'))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
        /** @var Session $sessionModel */
        $sessionModel = $this->_objectManager->create(SessionFactory::class)->create();
        $sessionModel->setCustomerId($this->customer->getId());
        $sessionModel->setUpdatedAt($lastUpdatedAt);
        $sessionModel->save();
        $event = new Event();
        $observer = new Observer(['event' => $event]);
        $event->setData('customer', $this->customer);
        $this->_persistentSession->setRememberMeChecked(true);
        $this->_model->execute($observer);
        /** @var Session $sessionModel */
        $sessionModel = $this->_objectManager->create(Session::class);
        $sessionModel->loadByCustomerId(1);
        $this->assertGreaterThan($lastUpdatedAt, $sessionModel->getUpdatedAt());
    }
}
