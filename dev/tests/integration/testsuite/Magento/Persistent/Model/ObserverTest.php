<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Model;

use Magento\Customer\Model\Context;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ObserverTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Persistent\Model\Observer
     */
    protected $_observer;

    /**
     * @var \Magento\Checkout\Model\Session | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_checkoutSession;

    protected function setUp(): void
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

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
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testEmulateWelcomeBlock()
    {
        $httpContext = new \Magento\Framework\App\Http\Context();
        $httpContext->setValue(Context::CONTEXT_AUTH, 1, 1);
        $block = $this->_objectManager->create(
            \Magento\Sales\Block\Reorder\Sidebar::class,
            [
                'httpContext' => $httpContext
            ]
        );
        $this->_observer->emulateWelcomeBlock($block);

        $this->assertEquals('&nbsp;', $block->getWelcome());
    }
}
