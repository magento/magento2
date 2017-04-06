<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Observer;

/**
 * @magentoDataFixture Magento/Persistent/_files/persistent.php
 */
class EmulateQuoteObserverTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Persistent\Observer\EmulateQuoteObserver
     */
    protected $_observer;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Checkout\Model\Session | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_checkoutSession;

    public function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->_customerSession = $this->_objectManager->get(\Magento\Customer\Model\Session::class);

        $this->customerRepository = $this->_objectManager->create(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        );

        $this->_checkoutSession = $this->getMockBuilder(
            \Magento\Checkout\Model\Session::class
        )->disableOriginalConstructor()->setMethods([])->getMock();

        $this->_persistentSessionHelper = $this->_objectManager->create(\Magento\Persistent\Helper\Session::class);

        $this->_observer = $this->_objectManager->create(
            \Magento\Persistent\Observer\EmulateQuoteObserver::class,
            [
                'customerRepository' => $this->customerRepository,
                'checkoutSession' => $this->_checkoutSession,
                'persistentSession' => $this->_persistentSessionHelper
            ]
        );
    }

    /**
     * @magentoConfigFixture current_store persistent/options/enabled 1
     * @magentoConfigFixture current_store persistent/options/remember_enabled 1
     * @magentoConfigFixture current_store persistent/options/remember_default 1
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store persistent/options/shopping_cart 1
     * @magentoConfigFixture current_store persistent/options/logout_clear 0
     */
    public function testEmulateQuote()
    {
        $requestMock = $this->getMockBuilder(
            \Magento\Framework\App\Request\Http::class
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $requestMock->expects($this->once())->method('getFullActionName')->will($this->returnValue('valid_action'));
        $event = new \Magento\Framework\Event(['request' => $requestMock]);
        $observer = new \Magento\Framework\Event\Observer();
        $observer->setEvent($event);

        $this->_customerSession->loginById(1);

        $customer = $this->customerRepository->getById(
            $this->_persistentSessionHelper->getSession()->getCustomerId()
        );
        $this->_checkoutSession->expects($this->once())->method('setCustomerData')->with($customer);
        $this->_customerSession->logout();

        $this->_observer->execute($observer);
    }
}
