<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer\Mapper;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test Magento\Customer\Block\Adminhtml\Edit\Tab\Addresses
 *
 * @magentoAppArea adminhtml
 * @magentoDataFixture Magento/Customer/_files/customer_sample.php
 */
class AddressesTest extends \PHPUnit_Framework_TestCase
{
    /** @var CustomerRepositoryInterface */
    private $_customerRepository;

    /** @var \Magento\Backend\Model\Session */
    private $_backendSession;

    /** @var  \Magento\Framework\ObjectManagerInterface */
    private $_objectManager;

    /** @var  array */
    private $_customerData;

    /**
     * @var Mapper
     */
    private $customerMapper;

    /**
     * @var \Magento\Customer\Model\Address\Mapper
     */
    private $addressMapper;

    public function setUp()
    {
        $this->_objectManager = Bootstrap::getObjectManager();
        $this->_customerRepository = $this->_objectManager->get(
            'Magento\Customer\Api\CustomerRepositoryInterface'
        );
        $this->_backendSession = $this->_objectManager->get('Magento\Backend\Model\Session');

        $this->customerMapper = $this->_objectManager->get(
            'Magento\Customer\Model\Customer\Mapper'
        );

        $this->addressMapper = $this->_objectManager->get(
            'Magento\Customer\Model\Address\Mapper'
        );
    }

    public function tearDown()
    {
        $this->_backendSession->unsCustomerData();
    }

    /**
     * Validate country default gets displayed
     */
    public function testInitFormEmpty()
    {
        $block = $this->_objectManager->create('Magento\Customer\Block\Adminhtml\Edit\Tab\Addresses');
        $this->_backendSession->setCustomerData(['account' => [], 'address' => []]);

        /** @var Addresses $block */
        $block = $block->initForm();
        /** @var \Magento\Framework\Data\Form $form */
        $form = $block->getForm();

        // Validate Country gets set
        $this->assertEquals('US', $form->getElement('country_id')->getValue());
    }

    public function testInitForm()
    {
        $this->setupExistingCustomerData();
        $block = $this->_objectManager->create('Magento\Customer\Block\Adminhtml\Edit\Tab\Addresses');

        /** @var Addresses $block */
        $block = $block->initForm();
        /** @var \Magento\Framework\Data\Form $form */
        $form = $block->getForm();

        $this->assertInstanceOf('Magento\Framework\Data\Form\Element\Fieldset', $form->getElement('address_fieldset'));
        $this->assertInstanceOf('Magento\Framework\Data\Form\Element\Text', $form->getElement('prefix'));
        $this->assertInstanceOf('Magento\Framework\Data\Form\Element\Text', $form->getElement('firstname'));
        $this->assertInstanceOf('Magento\Framework\Data\Form\Element\Text', $form->getElement('middlename'));
        $this->assertInstanceOf('Magento\Framework\Data\Form\Element\Text', $form->getElement('lastname'));
        $this->assertInstanceOf('Magento\Framework\Data\Form\Element\Text', $form->getElement('suffix'));
        $this->assertInstanceOf('Magento\Framework\Data\Form\Element\Text', $form->getElement('company'));
        $this->assertInstanceOf('Magento\Framework\Data\Form\Element\Multiline', $form->getElement('street'));
        $this->assertEquals(2, $form->getElement('street')->getLineCount());
        $this->assertInstanceOf('Magento\Framework\Data\Form\Element\Text', $form->getElement('city'));
        $this->assertInstanceOf('Magento\Framework\Data\Form\Element\Select', $form->getElement('country_id'));
        $this->assertEquals('US', $form->getElement('country_id')->getValue());
        $this->assertInstanceOf('Magento\Framework\Data\Form\Element\Text', $form->getElement('region'));
        $this->assertInstanceOf('Magento\Framework\Data\Form\Element\Hidden', $form->getElement('region_id'));
        $this->assertInstanceOf('Magento\Framework\Data\Form\Element\Text', $form->getElement('postcode'));
        $this->assertInstanceOf('Magento\Framework\Data\Form\Element\Text', $form->getElement('telephone'));
        $this->assertInstanceOf('Magento\Framework\Data\Form\Element\Text', $form->getElement('fax'));
        $this->assertInstanceOf('Magento\Framework\Data\Form\Element\Text', $form->getElement('vat_id'));
    }

    public function testToHtml()
    {
        $this->setupExistingCustomerData();
        /** @var \Magento\Customer\Block\Adminhtml\Edit\Tab\Addresses $block */
        $block = $this->_objectManager->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Customer\Block\Adminhtml\Edit\Tab\Addresses'
        );

        $html = $block->initForm()->toHtml();

        $this->assertContains('Customer Addresses', $html);
        $this->assertContains('Remove address', $html);
        $this->assertContains('Edit address', $html);
        $this->assertContains('test firstname test lastname', $html);
        $this->assertContains('test street', $html);
        $this->assertContains('removed street', $html);
        $this->assertContains('T: +7000000001', $html);
        $this->assertContains('Default Billing Address', $html);
        $this->assertContains('Default Shipping Address', $html);
        $this->assertContains('Add New Address', $html);
        $this->assertContains('<option value="US" selected="selected">United States</option>', $html);
        $this->assertContains('Texas', $html);

        $this->assertContains('<li class="address-list-item" id="address_item_1" data-item="1">', $html);
        $this->assertContains('<a href="#form_address_item_3"', $html);
        $this->assertContains('<div class="address-item-edit-content"', $html);
        $this->assertContains('id="form_address_item_1" data-item="1"', $html);
        $this->assertContains('{"name": "address_item_1"}}', $html);
        $this->assertContains('<input id="_item1prefix" name="address[1][prefix]"', $html);
    }

    /**
     * Put existing customer data into the backend session
     */
    protected function setupExistingCustomerData()
    {
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $this->_customerRepository->getById(1);
        $this->_customerData = [
            'customer_id' => $customer->getId(),
            'account' => $this->customerMapper->toFlatArray($customer),
        ];
        $this->_customerData['account']['id'] = $customer->getId();
        /** @var \Magento\Customer\Api\Data\AddressInterface[] $addresses */
        $addresses = $customer->getAddresses();
        foreach ($addresses as $addressData) {
            $this->_customerData['address'][$addressData->getId()] = $this->addressMapper->toFlatArray($addressData);
            $this->_customerData['address'][$addressData->getId()]['id'] = $addressData->getId();
        }
        $this->_backendSession->setCustomerData($this->_customerData);
    }
}
