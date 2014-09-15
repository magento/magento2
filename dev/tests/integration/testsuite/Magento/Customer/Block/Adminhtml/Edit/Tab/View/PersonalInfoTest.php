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
use Magento\Customer\Service\V1\CustomerGroupServiceInterface;
use Magento\Customer\Service\V1\Data\Customer;
use Magento\Customer\Service\V1\Data\CustomerBuilder;

/**
 * Magento\Customer\Block\Adminhtml\Edit\Tab\View
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoAppArea adminhtml
 */
class PersonalInfoTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Backend\Block\Template\Context */
    private $_context;

    /** @var  \Magento\Framework\Registry */
    private $_coreRegistry;

    /** @var  CustomerBuilder */
    private $_customerBuilder;

    /** @var  CustomerAccountServiceInterface */
    private $_customerAccountService;

    /** @var  CustomerGroupServiceInterface */
    private $_groupService;

    /** @var \Magento\Framework\StoreManagerInterface */
    private $_storeManager;

    /** @var \Magento\Framework\ObjectManager */
    private $_objectManager;

    /** @var  PersonalInfo */
    private $_block;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    public function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->_storeManager = $this->_objectManager->get('Magento\Framework\StoreManagerInterface');
        $this->_context = $this->_objectManager->get(
            'Magento\Backend\Block\Template\Context',
            array('storeManager' => $this->_storeManager)
        );

        $this->_customerBuilder = $this->_objectManager->get('Magento\Customer\Service\V1\Data\CustomerBuilder');
        $this->_coreRegistry = $this->_objectManager->get('Magento\Framework\Registry');
        $this->_customerAccountService = $this->_objectManager->get(
            'Magento\Customer\Service\V1\CustomerAccountServiceInterface'
        );
        $this->_groupService = $this->_objectManager->get('Magento\Customer\Service\V1\CustomerGroupServiceInterface');
        $this->dateTime = $this->_objectManager->get('\Magento\Framework\Stdlib\DateTime');

        $this->_block = $this->_objectManager->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Customer\Block\Adminhtml\Edit\Tab\View\PersonalInfo',
            '',
            array(
                'context' => $this->_context,
                'groupService' => $this->_groupService,
                'registry' => $this->_coreRegistry
            )
        );
    }

    public function tearDown()
    {
        $this->_coreRegistry->unregister(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetCustomer()
    {
        $this->assertEquals($this->_loadCustomer(), $this->_block->getCustomer());
    }

    public function testGetCustomerEmpty()
    {
        $this->assertEquals($this->_createCustomer(), $this->_block->getCustomer());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetGroupName()
    {
        $groupName = $this->_groupService->getGroup($this->_loadCustomer()->getGroupId())->getCode();
        $this->assertEquals($groupName, $this->_block->getGroupName());
    }

    public function testGetGroupNameNull()
    {
        $this->_createCustomer();
        $this->assertNull($this->_block->getGroupName());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetCreateDate()
    {
        $createdAt = $this->_block->formatDate(
            $this->_loadCustomer()->getCreatedAt(),
            \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_MEDIUM,
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
        $date = $this->_context->getLocaleDate()->scopeDate(
            $customer->getStoreId(),
            $this->dateTime->toTimestamp($customer->getCreatedAt()),
            true
        );
        $storeCreateDate = $this->_block->formatDate(
            $date,
            \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_MEDIUM,
            true
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
        $defaultTimeZonePath = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\TimezoneInterface')
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
        $this->assertEquals('Confirmed', $this->_block->getIsConfirmedStatus());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testIsConfirmedStatusConfirmationIsNotRequired()
    {
        /** @var Customer $customer */
        $customer = $this->_customerBuilder->setConfirmation(
            true
        )->setFirstname(
            'firstname'
        )->setLastname(
            'lastname'
        )->setEmail(
            'email@email.com'
        )->create();
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Customer\Service\V1\Data\CustomerDetailsBuilder $customerDetailsBuilder */
        $customerDetailsBuilder = $objectManager->create('Magento\Customer\Service\V1\Data\CustomerDetailsBuilder');
        $customerDetails = $customerDetailsBuilder->setCustomer($customer)->create();
        $customer = $this->_customerAccountService->createCustomer($customerDetails);
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
        $this->assertContains('John Smith<br/>', $html);
        $this->assertContains('Green str, 67<br />', $html);
        $this->assertContains('CityM,  Alabama, 75477<br/>', $html);
    }

    public function testGetBillingAddressHtmlNoDefaultAddress()
    {
        $this->_createCustomer();
        $this->assertEquals(
            __('The customer does not have default billing address.'),
            $this->_block->getBillingAddressHtml()
        );
    }

    /**
     * @return Customer
     */
    private function _createCustomer()
    {
        /** @var \Magento\Customer\Service\V1\Data\Customer $customer */
        $customer = $this->_customerBuilder->setFirstname(
            'firstname'
        )->setLastname(
            'lastname'
        )->setEmail(
            'email@email.com'
        )->create();
        $data = array('account' => $customer->__toArray());
        $this->_context->getBackendSession()->setCustomerData($data);
        return $customer;
    }

    /**
     * @return Customer
     */
    private function _loadCustomer()
    {
        $customer = $this->_customerAccountService->getCustomer(1);
        $data = array('account' => $customer->__toArray());
        $this->_context->getBackendSession()->setCustomerData($data);
        $this->_coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customer->getId());
        return $customer;
    }
}
