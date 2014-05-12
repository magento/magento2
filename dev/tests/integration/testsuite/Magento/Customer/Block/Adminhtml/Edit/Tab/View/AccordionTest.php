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
namespace Magento\Customer\Block\Adminhtml\Edit\Tab\View;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Service\V1\CustomerAccountServiceInterface;

/**
 * @magentoAppArea adminhtml
 */
class AccordionTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\View\Layout */
    protected $layout;

    /** @var \Magento\Framework\Registry */
    protected $registry;

    /** @var CustomerAccountServiceInterface */
    protected $customerAccountService;

    /** @var \Magento\Backend\Model\Session */
    protected $backendSession;

    protected function setUp()
    {
        parent::setUp();
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->registry = $objectManager->get('Magento\Framework\Registry');
        $this->customerAccountService = $objectManager->get(
            'Magento\Customer\Service\V1\CustomerAccountServiceInterface'
        );
        $this->backendSession = $objectManager->get('Magento\Backend\Model\Session');
        $this->layout = $objectManager->create(
            'Magento\Framework\View\Layout',
            array('area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE)
        );
    }

    protected function tearDown()
    {
        $this->registry->unregister(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoConfigFixture customer/account_share/scope 1
     */
    public function testToHtmlEmptyWebsiteShare()
    {
        $this->registry->register(RegistryConstants::CURRENT_CUSTOMER_ID, 1);
        $block = $this->layout->createBlock('Magento\Customer\Block\Adminhtml\Edit\Tab\View\Accordion');

        $html = $block->toHtml();

        $this->assertContains('Wishlist - 0 item(s)', $html);
        $this->assertContains('Shopping Cart - 0 item(s)', $html);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Core/_files/second_third_store.php
     * @magentoConfigFixture current_store customer/account_share/scope 0
     */
    public function testToHtmlEmptyGlobalShareAndSessionData()
    {
        $this->registry->register(RegistryConstants::CURRENT_CUSTOMER_ID, 1);
        $customer = $this->customerAccountService->getCustomer(1);
        $this->backendSession->setCustomerData(array('account' => $customer->__toArray()));
        $block = $this->layout->createBlock('Magento\Customer\Block\Adminhtml\Edit\Tab\View\Accordion');

        $html = $block->toHtml();

        $this->assertContains('Wishlist - 0 item(s)', $html);
        $this->assertContains('Shopping Cart of Main Website - 0 item(s)', $html);
        $this->assertContains('Shopping Cart of Second Website - 0 item(s)', $html);
        $this->assertContains('Shopping Cart of Third Website - 0 item(s)', $html);
    }

    /**
     * @magentoConfigFixture customer/account_share/scope 1
     */
    public function testToHtmlEmptyWebsiteShareNewCustomer()
    {
        $block = $this->layout->createBlock('Magento\Customer\Block\Adminhtml\Edit\Tab\View\Accordion');

        $html = $block->toHtml();

        $this->assertContains('Wishlist - 0 item(s)', $html);
        $this->assertContains('Shopping Cart - 0 item(s)', $html);
    }
}
