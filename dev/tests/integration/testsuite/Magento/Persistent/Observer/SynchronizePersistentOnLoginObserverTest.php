<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Observer;

/**
 * @magentoDataFixture Magento/Customer/_files/customer.php
 */
class SynchronizePersistentOnLoginObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Persistent\Observer\SynchronizePersistentOnLoginObserver
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
            'Magento\Persistent\Observer\SynchronizePersistentOnLoginObserver',
            [
                'persistentSession' => $this->_persistentSession,
                'customerSession' => $this->_customerSession
            ]
        );
    }

    /**
     * @covers \Magento\Persistent\Observer\SynchronizePersistentOnLoginObserver::execute
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
        $this->_model->execute($observer);

        // check that persistent session has been stored for Customer
        /** @var \Magento\Persistent\Model\Session $sessionModel */
        $sessionModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Persistent\Model\Session'
        );
        $sessionModel->loadByCustomerId(1);
        $this->assertEquals(1, $sessionModel->getCustomerId());
    }
}
