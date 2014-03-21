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
use Magento\Customer\Service\V1\CustomerGroupServiceInterface;
use Magento\Customer\Service\V1\Data\Customer;
use Magento\Customer\Service\V1\Data\CustomerBuilder;

/**
 * Magento\Customer\Block\Adminhtml\Edit\Tab\View
 *
 * @magentoAppArea adminhtml
 */
class ViewTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Backend\Block\Template\Context */
    private $_context;

    /** @var  \Magento\Registry */
    private $_coreRegistry;

    /** @var  CustomerBuilder */
    private $_customerBuilder;

    /** @var  CustomerAccountServiceInterface */
    private $_customerAccountService;

    /** @var  CustomerGroupServiceInterface */
    private $_groupService;

    /** @var \Magento\Core\Model\StoreManagerInterface */
    private $_storeManager;

    /** @var  View */
    private $_block;

    public function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->_storeManager = $objectManager->get('Magento\Core\Model\StoreManager');
        $this->_context = $objectManager->get(
            'Magento\Backend\Block\Template\Context',
            array('storeManager' => $this->_storeManager)
        );

        $this->_customerBuilder = $objectManager->get('Magento\Customer\Service\V1\Data\CustomerBuilder');
        $this->_coreRegistry = $objectManager->get('Magento\Registry');
        $this->_customerAccountService = $objectManager->get(
            'Magento\Customer\Service\V1\CustomerAccountServiceInterface'
        );
        $this->_groupService = $objectManager->get('Magento\Customer\Service\V1\CustomerGroupServiceInterface');

        $this->_block = $objectManager->get(
            'Magento\View\LayoutInterface'
        )->createBlock(
            'Magento\Customer\Block\Adminhtml\Edit\Tab\View',
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
            \Magento\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_MEDIUM,
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
        $date = $this->_context->getLocaleDate()->scopeDate($customer->getStoreId(), $customer->getCreatedAt(), true);
        $storeCreateDate = $this->_block->formatDate(
            $date,
            \Magento\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_MEDIUM,
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
         * @var \Magento\Stdlib\DateTime\TimezoneInterface $defaultTimeZonePath
         */
        $defaultTimeZonePath = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Stdlib\DateTime\TimezoneInterface'
        )->getDefaultTimezonePath();
        $timezone = $this->_context->getStoreConfig()->getConfig(
            $defaultTimeZonePath,
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
        $id = $this->_customerAccountService->saveCustomer($customer);
        $this->_coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $id);
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
     */
    public function testGetStoreId()
    {
        $this->assertEquals($this->_loadCustomer()->getStoreId(), $this->_block->getStoreId());
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

    public function testGetTabLabel()
    {
        $this->assertEquals(__('Customer View'), $this->_block->getTabLabel());
    }

    public function testGetTabTitle()
    {
        $this->assertEquals(__('Customer View'), $this->_block->getTabTitle());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testCanShowTab()
    {
        $this->_loadCustomer();
        $this->assertTrue($this->_block->canShowTab());
    }

    public function testCanShowTabNot()
    {
        $this->_createCustomer();
        $this->assertFalse($this->_block->canShowTab());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testIsHiddenNot()
    {
        $this->_loadCustomer();
        $this->assertFalse($this->_block->isHidden());
    }

    public function testIsHidden()
    {
        $this->_createCustomer();
        $this->assertTrue($this->_block->isHidden());
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
