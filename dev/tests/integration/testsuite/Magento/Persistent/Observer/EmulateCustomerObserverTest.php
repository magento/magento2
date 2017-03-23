<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Observer;

/**
 * @magentoDataFixture Magento/Persistent/_files/persistent.php
 */
class EmulateCustomerObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Persistent\Helper\Session
     */
    protected $_persistentSessionHelper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Persistent\Observer\EmulateCustomerObserver
     */
    protected $_observer;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    public function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->_customerSession = $this->_objectManager->get(\Magento\Customer\Model\Session::class);

        $this->customerRepository = $this->_objectManager->create(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        );
        $this->_persistentSessionHelper = $this->_objectManager->create(\Magento\Persistent\Helper\Session::class);

        $this->_observer = $this->_objectManager->create(
            \Magento\Persistent\Observer\EmulateCustomerObserver::class,
            [
                'customerRepository' => $this->customerRepository,
                'persistentSession' => $this->_persistentSessionHelper
            ]
        );
    }

    /**
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store persistent/options/shopping_cart 1
     * @magentoConfigFixture current_store persistent/options/logout_clear 0
     * @magentoConfigFixture current_store persistent/options/enabled 1
     */
    public function testEmulateCustomer()
    {
        $observer = new \Magento\Framework\Event\Observer();

        $this->_customerSession->loginById(1);
        $this->_customerSession->logout();
        $this->assertNull($this->_customerSession->getCustomerId());
        $this->assertEquals(
            \Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID,
            $this->_customerSession->getCustomerGroupId()
        );

        $this->_observer->execute($observer);
        $customer = $this->customerRepository->getById(
            $this->_persistentSessionHelper->getSession()->getCustomerId()
        );
        $this->assertEquals(
            $customer->getId(),
            $this->_customerSession->getCustomerId()
        );
        $this->assertEquals(
            $customer->getGroupId(),
            $this->_customerSession->getCustomerGroupId()
        );
    }
}
