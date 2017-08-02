<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Model;

use Magento\Customer\Model\Context;

/**
 * @magentoDataFixture Magento/Persistent/_files/persistent.php
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Helper\View
     */
    protected $_customerViewHelper;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

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
     * @var \Magento\Persistent\Model\Observer
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

        $this->_customerViewHelper = $this->_objectManager->create(
            \Magento\Customer\Helper\View::class
        );
        $this->_escaper = $this->_objectManager->create(
            \Magento\Framework\Escaper::class
        );

        $this->customerRepository = $this->_objectManager->create(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        );

        $this->_checkoutSession = $this->getMockBuilder(
            \Magento\Checkout\Model\Session::class
        )->disableOriginalConstructor()->setMethods([])->getMock();

        $this->_persistentSessionHelper = $this->_objectManager->create(\Magento\Persistent\Helper\Session::class);

        $this->_observer = $this->_objectManager->create(
            \Magento\Persistent\Model\Observer::class,
            [
                'escaper' => $this->_escaper,
                'customerViewHelper' => $this->_customerViewHelper,
                'customerRepository' => $this->customerRepository,
                'checkoutSession' => $this->_checkoutSession
            ]
        );
    }

    /**
     * @magentoConfigFixture current_store persistent/options/enabled 1
     * @magentoConfigFixture current_store persistent/options/remember_enabled 1
     * @magentoConfigFixture current_store persistent/options/remember_default 1
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testEmulateWelcomeBlock()
    {
        $this->_customerSession->loginById(1);

        $httpContext = new \Magento\Framework\App\Http\Context();
        $httpContext->setValue(Context::CONTEXT_AUTH, 1, 1);
        $block = $this->_objectManager->create(
            \Magento\Sales\Block\Reorder\Sidebar::class,
            [
                'httpContext' => $httpContext
            ]
        );
        $this->_observer->emulateWelcomeBlock($block);
        $customerName = $this->_escaper->escapeHtml(
            $this->_customerViewHelper->getCustomerName(
                $this->customerRepository->getById(
                    $this->_persistentSessionHelper->getSession()->getCustomerId()
                )
            )
        );
        $translation = __('Welcome, %1!', $customerName)->__toString();
        $this->assertStringMatchesFormat('%A' . $translation . '%A', $block->getWelcome()->__toString());
        $this->_customerSession->logout();
    }
}
