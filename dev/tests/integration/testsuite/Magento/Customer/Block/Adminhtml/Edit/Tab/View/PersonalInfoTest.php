<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab\View;

use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Controller\RegistryConstants;

/**
 * Magento\Customer\Block\Adminhtml\Edit\Tab\View
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoAppArea adminhtml
 */
class PersonalInfoTest extends \PHPUnit\Framework\TestCase
{
    /** @var  \Magento\Backend\Block\Template\Context */
    private $_context;

    /** @var  \Magento\Framework\Registry */
    private $_coreRegistry;

    /** @var  CustomerInterfaceFactory */
    private $_customerFactory;

    /** @var  \Magento\Customer\Api\CustomerRepositoryInterface */
    private $_customerRepository;

    /** @var  \Magento\Customer\Api\GroupRepositoryInterface */
    private $_groupRepository;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    private $_storeManager;

    /** @var \Magento\Framework\ObjectManagerInterface */
    private $_objectManager;

    /** @var \Magento\Framework\Reflection\DataObjectProcessor */
    private $_dataObjectProcessor;

    /** @var  PersonalInfo */
    private $_block;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    public function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->_storeManager = $this->_objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $this->_context = $this->_objectManager->get(
            \Magento\Backend\Block\Template\Context::class,
            ['storeManager' => $this->_storeManager]
        );

        $this->_customerFactory = $this->_objectManager->get(
            \Magento\Customer\Api\Data\CustomerInterfaceFactory::class
        );
        $this->_coreRegistry = $this->_objectManager->get(\Magento\Framework\Registry::class);
        $this->_customerRepository = $this->_objectManager->get(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        );
        $this->_dataObjectProcessor = $this->_objectManager->get(
            \Magento\Framework\Reflection\DataObjectProcessor::class
        );

        $this->_groupRepository = $this->_objectManager->get(\Magento\Customer\Api\GroupRepositoryInterface::class);
        $this->dateTime = $this->_objectManager->get(\Magento\Framework\Stdlib\DateTime::class);

        $this->_block = $this->_objectManager->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Customer\Block\Adminhtml\Edit\Tab\View\PersonalInfo::class,
            '',
            [
                'context' => $this->_context,
                'groupService' => $this->_groupRepository,
                'registry' => $this->_coreRegistry
            ]
        );
    }

    public function tearDown()
    {
        $this->_coreRegistry->unregister(RegistryConstants::CURRENT_CUSTOMER_ID);
        /** @var \Magento\Customer\Model\CustomerRegistry $customerRegistry */
        $customerRegistry = $this->_objectManager->get(\Magento\Customer\Model\CustomerRegistry::class);
        //Cleanup customer from registry
        $customerRegistry->remove(1);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetCustomer()
    {
        $expectedCustomer = $this->_loadCustomer();
        $expectedCustomerData = $this->_dataObjectProcessor->buildOutputDataArray(
            $expectedCustomer,
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $actualCustomer = $this->_block->getCustomer();
        $actualCustomerData = $this->_dataObjectProcessor->buildOutputDataArray(
            $actualCustomer,
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        foreach ($expectedCustomerData as $property => $value) {
            $expectedValue = is_numeric($value) ? (int)$value : $value;
            $actualValue = isset($actualCustomerData[$property]) ? $actualCustomerData[$property] : null;
            $actualValue = is_numeric($actualValue) ? (int)$actualValue : $actualValue;
            $this->assertEquals($expectedValue, $actualValue);
        }
    }

    public function testGetCustomerEmpty()
    {
        $expectedCustomer = $this->createCustomerAndAddToBackendSession();
        $actualCustomer = $this->_block->getCustomer();
        $this->assertEquals($expectedCustomer->getExtensionAttributes(), $actualCustomer->getExtensionAttributes());
        $this->assertEquals($expectedCustomer, $actualCustomer);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetGroupName()
    {
        $groupName = $this->_groupRepository->getById($this->_loadCustomer()->getGroupId())->getCode();
        $this->assertEquals($groupName, $this->_block->getGroupName());
    }

    public function testGetGroupNameNull()
    {
        $this->createCustomerAndAddToBackendSession();
        $this->assertNull($this->_block->getGroupName());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetCreateDate()
    {
        $createdAt = $this->_block->formatDate(
            $this->_loadCustomer()->getCreatedAt(),
            \IntlDateFormatter::MEDIUM,
            true
        );
        $this->assertEquals($createdAt, $this->_block->getCreateDate());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetStoreCreateDate()
    {
        $customer = $this->_loadCustomer();
        $localeDate = $this->_context->getLocaleDate();
        $timezone = $localeDate->getConfigTimezone(
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $customer->getStoreId()
        );
        $storeCreateDate = $this->_block->formatDate(
            $customer->getCreatedAt(),
            \IntlDateFormatter::MEDIUM,
            true,
            null,
            $timezone
        );
        $this->assertEquals($storeCreateDate, $this->_block->getStoreCreateDate());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetStoreCreateDateTimezone()
    {
        /**
         * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface $defaultTimeZonePath
         */
        $defaultTimeZonePath = $this->_objectManager->get(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)
            ->getDefaultTimezonePath();
        $timezone = $this->_context->getScopeConfig()->getValue(
            $defaultTimeZonePath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_loadCustomer()->getStoreId()
        );
        $this->assertEquals($timezone, $this->_block->getStoreCreateDateTimezone());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testIsConfirmedStatusConfirmed()
    {
        $this->_loadCustomer();
        $this->assertEquals(__('Confirmation Not Required'), $this->_block->getIsConfirmedStatus());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testIsConfirmedStatusConfirmationIsNotRequired()
    {
        $password = 'password';
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $this->_customerFactory->create()->setConfirmation(
            true
        )->setFirstname(
            'firstname'
        )->setLastname(
            'lastname'
        )->setEmail(
            'email@email.com'
        );
        $customer = $this->_customerRepository->save($customer, $password);
        $this->_coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customer->getId());
        $this->assertEquals('Confirmation Not Required', $this->_block->getIsConfirmedStatus());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetCreatedInStore()
    {
        $storeName = $this->_storeManager->getStore($this->_loadCustomer()->getStoreId())->getName();
        $this->assertEquals($storeName, $this->_block->getCreatedInStore());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testGetBillingAddressHtml()
    {
        $this->_loadCustomer();
        $html = $this->_block->getBillingAddressHtml();
        $this->assertContains('John Smith<br />', $html);
        $this->assertContains('Green str, 67<br />', $html);
        $this->assertContains('CityM,  Alabama, 75477<br />', $html);
    }

    public function testGetBillingAddressHtmlNoDefaultAddress()
    {
        $this->createCustomerAndAddToBackendSession();
        $this->assertEquals(
            __('The customer does not have default billing address.'),
            $this->_block->getBillingAddressHtml()
        );
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    private function createCustomerAndAddToBackendSession()
    {
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $this->_customerFactory->create()->setFirstname(
            'firstname'
        )->setLastname(
            'lastname'
        )->setEmail(
            'email@email.com'
        );
        $data = ['account' => $this->_dataObjectProcessor
            ->buildOutputDataArray($customer, \Magento\Customer\Api\Data\CustomerInterface::class), ];
        $this->_context->getBackendSession()->setCustomerData($data);
        return $customer;
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    private function _loadCustomer()
    {
        $customer = $this->_customerRepository->getById(1);
        $data = ['account' => $this->_dataObjectProcessor
            ->buildOutputDataArray($customer, \Magento\Customer\Api\Data\CustomerInterface::class), ];
        $this->_context->getBackendSession()->setCustomerData($data);
        $this->_coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customer->getId());
        return $customer;
    }
}
