<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Model\Observer;

/**
 * @magentoDataFixture Magento/Customer/_files/customer.php
 */
class SessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Persistent\Model\Observer\Session
     */
    protected $_model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
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

    public function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_persistentSession = $this->_objectManager->get('Magento\Persistent\Helper\Session');
        $this->_customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');
        $this->_model = $this->_objectManager->create(
            'Magento\Persistent\Model\Observer\Session',
            [
                'persistentSession' => $this->_persistentSession,
                'customerSession' => $this->_customerSession
            ]
        );
    }

    /**
     * @covers \Magento\Persistent\Model\Observer\Session::synchronizePersistentOnLogin
     */
    public function testSynchronizePersistentOnLogin()
    {
        $event = new \Magento\Framework\Event();
        $observer = new \Magento\Framework\Event\Observer(['event' => $event]);

        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
        $customerRepository = $this->_objectManager->create(
            'Magento\Customer\Api\CustomerRepositoryInterface'
        );

        /** @var $customer \Magento\Customer\Api\Data\CustomerInterface */
        $customer = $customerRepository->getById(1);
        $event->setData('customer', $customer);
        $this->_persistentSession->setRememberMeChecked(true);
        $this->_model->synchronizePersistentOnLogin($observer);

        // check that persistent session has been stored for Customer
        /** @var \Magento\Persistent\Model\Session $sessionModel */
        $sessionModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Persistent\Model\Session'
        );
        $sessionModel->loadByCustomerId(1);
        $this->assertEquals(1, $sessionModel->getCustomerId());
    }

    /**
     * @magentoConfigFixture current_store persistent/options/enabled 1
     * @magentoConfigFixture current_store persistent/options/logout_clear 1
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testSynchronizePersistentOnLogout()
    {
        $this->_customerSession->loginById(1);

        // check that persistent session has been stored for Customer
        /** @var \Magento\Persistent\Model\Session $sessionModel */
        $sessionModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Persistent\Model\Session'
        );
        $sessionModel->loadByCookieKey();
        $this->assertEquals(1, $sessionModel->getCustomerId());

        $this->_customerSession->logout();

        /** @var \Magento\Persistent\Model\Session $sessionModel */
        $sessionModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Persistent\Model\Session'
        );
        $sessionModel->loadByCookieKey();
        $this->assertNull($sessionModel->getCustomerId());
    }
}
