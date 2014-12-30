<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Controller\RegistryConstants;

/**
 * Test for Account
 *
 * @magentoAppArea adminhtml
 */
class AccountTest extends \PHPUnit_Framework_TestCase
{
    /** @var Account */
    protected $accountBlock;

    /** @var \Magento\Framework\ObjectManagerInterface */
    protected $objectManager;

    /** @var \Magento\Framework\Registry */
    protected $coreRegistry;

    /** @var \Magento\Backend\Model\Session */
    protected $backendSession;

    /** @var  \Magento\Backend\Block\Template\Context */
    protected $context;

    /** @var CustomerRepositoryInterface */
    protected $customerRepository;

    /** @var \Magento\Framework\Reflection\DataObjectProcessor */
    protected $dataObjectProcessor;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->coreRegistry = $this->objectManager->get('Magento\Framework\Registry');
        $this->coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, 1);
        $this->backendSession = $this->objectManager->get('Magento\Backend\Model\Session');
        $this->dataObjectProcessor = $this->objectManager->get('Magento\Framework\Reflection\DataObjectProcessor');

        $this->context = $this->objectManager->get(
            'Magento\Backend\Block\Template\Context',
            ['backendSession' => $this->backendSession]
        );

        $this->accountBlock = $this->objectManager->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Customer\Block\Adminhtml\Edit\Tab\Account',
            '',
            ['context' => $this->context]
        );

        $this->customerRepository = $this->objectManager->get(
            'Magento\Customer\Api\CustomerRepositoryInterface'
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
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $this->customerRepository->getById(1);
        $customerData = $this->dataObjectProcessor->buildOutputDataArray($customer, get_class($customer));
        $this->backendSession->setCustomerData(
            ['customer_id' => 1, 'account' => $customerData]
        );

        $result = $this->accountBlock->initForm()->toHtml();

        // Verify account email
        $this->assertRegExp('/id="_accountemail"[^>]*value="customer@example.com"/', $result);
        $this->assertRegExp('/input id="_accountfirstname"[^>]*value="John"/', $result);

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
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $this->customerRepository->getById(1);
        $customerData = $this->dataObjectProcessor->buildOutputDataArray($customer, get_class($customer));
        $this->backendSession->setCustomerData(
            ['customer_id' => 1, 'account' => $customerData]
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
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $this->customerRepository->getById(1);
        $customerData = $this->dataObjectProcessor->buildOutputDataArray($customer, get_class($customer));
        $this->backendSession->setCustomerData(
            [
                'customer_id' => 1,
                'account' => array_merge(
                    $customerData,
                    ['prefix' => 'Mr']
                ),
            ]
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
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $this->customerRepository->getById(1);
        $customerData = $this->dataObjectProcessor->buildOutputDataArray($customer, get_class($customer));
        $this->backendSession->setCustomerData(
            ['customer_id' => 1, 'account' => $customerData]
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
        /** @var \Magento\Customer\Api\Data\CustomerDataBuilder $customerBuilder */
        $customerBuilder = $this->objectManager->get('Magento\Customer\Api\Data\CustomerDataBuilder');
        $customerData = $this->dataObjectProcessor
            ->buildOutputDataArray($customerBuilder->create(), '\Magento\Customer\Api\Data\CustomerInterface');
        $this->backendSession->setCustomerData(
            ['customer_id' => 0, 'account' => $customerData]
        );
        $result = $this->accountBlock->initForm()->toHtml();

        // Contains send email controls
        $this->assertContains('<input id="_accountsendemail"', $result);
        $this->assertContains('<select id="_accountsendemail_store_id"', $result);
    }
}
