<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Model\Observer;

/**
 * @magentoDataFixture Magento/Persistent/_files/persistent.php
 */
class EmulateCustomerTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Persistent\Model\Observer\EmulateCustomer
     */
    protected $_observer;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    public function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->_customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');

        $this->customerRepository = $this->_objectManager->create(
            'Magento\Customer\Api\CustomerRepositoryInterface'
        );
        $this->_persistentSessionHelper = $this->_objectManager->create('Magento\Persistent\Helper\Session');

        $this->_observer = $this->_objectManager->create(
            'Magento\Persistent\Model\Observer\EmulateCustomer',
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
