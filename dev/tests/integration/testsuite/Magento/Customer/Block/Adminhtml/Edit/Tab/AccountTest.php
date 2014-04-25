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
namespace Magento\Customer\Block\Adminhtml\Edit\Tab;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Service\V1\CustomerAccountServiceInterface;

/**
 * Test for Account
 *
 * @magentoAppArea adminhtml
 */
class AccountTest extends \PHPUnit_Framework_TestCase
{
    /** @var Account */
    protected $accountBlock;

    /** @var \Magento\Framework\ObjectManager */
    protected $objectManager;

    /** @var \Magento\Framework\Registry */
    protected $coreRegistry;

    /** @var \Magento\Backend\Model\Session */
    protected $backendSession;

    /** @var  \Magento\Backend\Block\Template\Context */
    protected $context;

    /** @var CustomerAccountServiceInterface */
    protected $customerAccountService;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->coreRegistry = $this->objectManager->get('Magento\Framework\Registry');
        $this->coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, 1);
        $this->backendSession = $this->objectManager->get('Magento\Backend\Model\Session');

        $this->context = $this->objectManager->get(
            'Magento\Backend\Block\Template\Context',
            array('backendSession' => $this->backendSession)
        );

        $this->accountBlock = $this->objectManager->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Customer\Block\Adminhtml\Edit\Tab\Account',
            '',
            array('context' => $this->context)
        );

        $this->customerAccountService = $this->objectManager->get(
            'Magento\Customer\Service\V1\CustomerAccountServiceInterface'
        );
    }

    public function tearDown()
    {
        $this->coreRegistry->unregister(RegistryConstants::CURRENT_CUSTOMER_ID);
        /** @var \Magento\Customer\Model\CustomerRegistry $customerRegistry */
        $customerRegistry = $this->objectManager->get('Magento\Customer\Model\CustomerRegistry');
        //Cleanup customer from registry
        $customerRegistry->remove(1);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testToHtml()
    {
        $this->backendSession->setCustomerData(
            array('customer_id' => 1, 'account' => $this->customerAccountService->getCustomer(1)->__toArray())
        );

        $result = $this->accountBlock->initForm()->toHtml();

        // Verify account email
        $this->assertRegExp('/id="_accountemail"[^>]*value="customer@example.com"/', $result);
        $this->assertRegExp('/input id="_accountfirstname"[^>]*value="Firstname"/', $result);

        // Verify confirmation controls are not present
        $this->assertNotContains('field-confirmation', $result);
        $this->assertNotContains('_accountconfirmation', $result);

        // Prefix is present but empty
        $this->assertRegExp('/<input id="_accountprefix"[^>]*value=""/', $result);

        // Does not contain send email controls
        $this->assertNotContains('<input id="_accountsendemail"', $result);
        $this->assertNotContains('<select id="_accountsendemail_store_id"', $result);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/inactive_customer.php
     */
    public function testNeedsConfirmation()
    {
        $this->backendSession->setCustomerData(
            array('customer_id' => 1, 'account' => $this->customerAccountService->getCustomer(1)->__toArray())
        );

        $result = $this->accountBlock->initForm()->toHtml();

        // Verify confirmation controls are present
        $this->assertContains('<div class="field field-confirmation "', $result);
        $this->assertContains('<select id="_accountconfirmation"', $result);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testPrefix()
    {
        $this->backendSession->setCustomerData(
            array(
                'customer_id' => 1,
                'account' => array_merge(
                    $this->customerAccountService->getCustomer(1)->__toArray(),
                    array('prefix' => 'Mr')
                )
            )
        );
        $result = $this->accountBlock->initForm()->toHtml();

        // Prefix has value
        $this->assertRegExp('/<input id="_accountprefix"[^>]*value="Mr"/', $result);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testNotReadOnly()
    {
        $this->backendSession->setCustomerData(
            array('customer_id' => 1, 'account' => $this->customerAccountService->getCustomer(1)->__toArray())
        );

        $this->accountBlock->initForm()->toHtml();
        $element = $this->accountBlock->getForm()->getElement('firstname');

        // Make sure readonly has not been set (is null) or set to false
        $this->assertTrue(is_null($element->getReadonly()) || !$element->getReadonly());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testNewCustomer()
    {
        $customerBuilder = $this->objectManager->get('\Magento\Customer\Service\V1\Data\CustomerBuilder');
        $this->backendSession->setCustomerData(
            array('customer_id' => 0, 'account' => $customerBuilder->create()->__toArray())
        );
        $result = $this->accountBlock->initForm()->toHtml();

        // Contains send email controls
        $this->assertContains('<input id="_accountsendemail"', $result);
        $this->assertContains('<select id="_accountsendemail_store_id"', $result);
    }
}
