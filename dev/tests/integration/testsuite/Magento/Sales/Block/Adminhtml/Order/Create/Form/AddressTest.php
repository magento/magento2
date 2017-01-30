<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Form;

/**
 * Test class for \Magento\Sales\Block\Adminhtml\Order\Create\Form\Address
 *
 * @magentoAppArea adminhtml
 */
class AddressTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\ObjectManagerInterface */
    protected $_objectManager;

    /** @var \Magento\Sales\Block\Adminhtml\Order\Create\Form\Address */
    protected $_addressBlock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Customer\Api\AddressRepositoryInterface */
    protected $addressRepository;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->addressRepository = $this->getMockForAbstractClass(
            'Magento\Customer\Api\AddressRepositoryInterface',
            [],
            '',
            false,
            true,
            true,
            ['getList']
        );
        /** @var \Magento\Framework\View\LayoutInterface $layout */
        $layout = $this->_objectManager->get('Magento\Framework\View\LayoutInterface');
        $sessionQuoteMock = $this->getMockBuilder('Magento\Backend\Model\Session\Quote')
            ->disableOriginalConstructor()->setMethods(['getCustomerId', 'getStore', 'getStoreId', 'getQuote'])
            ->getMock();
        $sessionQuoteMock->expects($this->any())->method('getCustomerId')->will($this->returnValue(1));

        $this->_addressBlock = $layout->createBlock(
            'Magento\Sales\Block\Adminhtml\Order\Create\Form\Address',
            'address_block' . rand(),
            ['addressService' => $this->addressRepository, 'sessionQuote' => $sessionQuoteMock]
        );
        parent::setUp();
    }

    public function testGetAddressCollection()
    {
        $addressData = $this->_getAddresses();
        $searchResult = $this->getMockForAbstractClass(
            'Magento\Customer\Api\Data\AddressSearchResultsInterface',
            [],
            '',
            false,
            true,
            true,
            ['getItems']
        );
        $searchResult->expects($this->any())
            ->method('getItems')
            ->will($this->returnValue($addressData));
        $this->addressRepository->expects($this->any())
            ->method('getList')
            ->will($this->returnValue($searchResult));
        $this->assertEquals($addressData, $this->_addressBlock->getAddressCollection());
    }

    public function testGetAddressCollectionJson()
    {
        $addressData = $this->_getAddresses();
        $searchResult = $this->getMockForAbstractClass(
            'Magento\Customer\Api\Data\AddressSearchResultsInterface',
            [],
            '',
            false,
            true,
            true,
            ['getItems']
        );
        $searchResult->expects($this->any())
            ->method('getItems')
            ->will($this->returnValue($addressData));
        $this->addressRepository->expects($this->any())
            ->method('getList')
            ->will($this->returnValue($searchResult));
        $expectedOutput = '[
            {
                "firstname": false,
                "lastname": false,
                "company": false,
                "street": "",
                "city": false,
                "country_id": "US",
                "region": false,
                "region_id": false,
                "postcode": false,
                "telephone": false,
                "vat_id": false
            },
            {
                "firstname": "FirstName1",
                "lastname": "LastName1",
                "company": false,
                "street": "Street1",
                "city": false,
                "country_id": false,
                "region": false,
                "region_id": false,
                "postcode": false,
                "telephone": false,
                "vat_id": false
            },
            {
                "firstname": "FirstName2",
                "lastname": "LastName2",
                "company": false,
                "street": "Street2",
                "city": false,
                "country_id": false,
                "region": false,
                "region_id": false,
                "postcode": false,
                "telephone": false,
                "vat_id": false
            }
        ]';
        $expectedOutput = str_replace(['    ', "\n", "\r"], '', $expectedOutput);
        $expectedOutput = str_replace(': ', ':', $expectedOutput);

        $this->assertEquals($expectedOutput, $this->_addressBlock->getAddressCollectionJson());
    }

    public function testGetAddressAsString()
    {
        $address = $this->_getAddresses()[0];
        $expectedResult = "FirstName1 LastName1, Street1, ,  , ";
        $this->assertEquals($expectedResult, $this->_addressBlock->getAddressAsString($address));
    }

    /**
     * Test \Magento\Sales\Block\Adminhtml\Order\Create\Form\Address::_prepareForm() indirectly.
     */
    public function testGetForm()
    {
        $expectedFields = [
            'prefix',
            'firstname',
            'middlename',
            'lastname',
            'suffix',
            'company',
            'street',
            'city',
            'country_id',
            'region',
            'region_id',
            'postcode',
            'telephone',
            'fax',
            'vat_id',
        ];
        $form = $this->_addressBlock->getForm();
        $this->assertEquals(1, $form->getElements()->count(), "Form has invalid number of fieldsets");
        /** @var \Magento\Framework\Data\Form\Element\Fieldset $fieldset */
        $fieldset = $form->getElements()[0];
        $this->assertEquals(
            count($expectedFields),
            $fieldset->getElements()->count(),
            "Form has invalid number of fields"
        );
        /** @var \Magento\Framework\Data\Form\Element\AbstractElement $element */
        foreach ($fieldset->getElements() as $element) {
            $this->assertTrue(
                in_array($element->getId(), $expectedFields),
                sprintf('Unexpected field "%s" in form.', $element->getId())
            );
        }

        /** @var \Magento\Framework\Data\Form\Element\Select $countryIdField */
        $countryIdField = $fieldset->getElements()->searchById('country_id');
        $this->assertSelectCount('option', 246, $countryIdField->getElementHtml());
    }

    /**
     * @return \Magento\Customer\Api\Data\AddressInterface[]
     */
    protected function _getAddresses()
    {
        /** @var \Magento\Customer\Api\Data\AddressInterfaceFactory $addressFactory */
        $addressFactory = $this->_objectManager->create('Magento\Customer\Api\Data\AddressInterfaceFactory');
        $addressData[] = $addressFactory->create()
            ->setId(1)
            ->setStreet(['Street1'])
            ->setFirstname('FirstName1')
            ->setLastname('LastName1');
        $addressData[] = $addressFactory->create()
            ->setId(2)
            ->setStreet(['Street2'])
            ->setFirstname('FirstName2')
            ->setLastname('LastName2');
        return $addressData;
    }
}
