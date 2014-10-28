<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Persistent\Model;

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
     * @var \Magento\Customer\Service\V1\CustomerAccountServiceInterface
     */
    protected $_customerAccountService;

    /**
     * @var \Magento\Persistent\Helper\Session
     */
    protected $_persistentSessionHelper;

    /**
     * @var \Magento\Framework\ObjectManager
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

        $this->_customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');

        $this->_customerViewHelper = $this->_objectManager->create(
            'Magento\Customer\Helper\View'
        );
        $this->_escaper = $this->_objectManager->create(
            'Magento\Framework\Escaper'
        );
        $this->_customerAccountService = $this->_objectManager->create(
            'Magento\Customer\Service\V1\CustomerAccountServiceInterface'
        );

        $this->_checkoutSession = $this->getMockBuilder(
            'Magento\Checkout\Model\Session'
        )->disableOriginalConstructor()->setMethods([])->getMock();

        $this->_persistentSessionHelper = $this->_objectManager->create('Magento\Persistent\Helper\Session');

        $this->_observer = $this->_objectManager->create(
            'Magento\Persistent\Model\Observer',
            [
                'escaper' => $this->_escaper,
                'customerViewHelper' => $this->_customerViewHelper,
                'customerAccountService' => $this->_customerAccountService,
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
        $httpContext->setValue(\Magento\Customer\Helper\Data::CONTEXT_AUTH, 1, 1);
        $block = $this->_objectManager->create(
            'Magento\Sales\Block\Reorder\Sidebar',
            [
                'httpContext' => $httpContext
            ]
        );
        $this->_observer->emulateWelcomeBlock($block);
        $customerName = $this->_escaper->escapeHtml(
            $this->_customerViewHelper->getCustomerName(
                $this->_customerAccountService->getCustomer(
                    $this->_persistentSessionHelper->getSession()->getCustomerId()
                )
            )
        );
        $translation = __('Welcome, %1!', $customerName);
        $this->assertStringMatchesFormat('%A' . $translation . '%A', $block->getWelcome());
        $this->_customerSession->logout();
    }
}
