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

namespace Magento\Customer\Service\V1;

/**
 * \Magento\Customer\Service\V1\CustomerAddressService
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerAddressServiceTest extends \PHPUnit_Framework_TestCase
{

    const STREET = 'Parmer';
    const CITY = 'Albuquerque';
    const POSTCODE = '90014';
    const TELEPHONE = '7143556767';
    const REGION = 'Alabama';
    const REGION_ID = 1;
    const COUNTRY_ID = 'US';

    /** Sample values for testing */
    const ID = 1;
    const FIRSTNAME = 'Jane';
    const LASTNAME = 'Doe';
    const NAME = 'J';
    const EMAIL = 'janedoe@example.com';
    const EMAIL_CONFIRMATION_KEY = 'blj487lkjs4confirmation_key';
    const PASSWORD = 'password';
    const ATTRIBUTE_CODE = 'random_attr_code';
    const ATTRIBUTE_VALUE = 'random_attr_value';
    const WEBSITE_ID = 1;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Model\CustomerFactory
     */
    private $_customerFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Model\AddressFactory
     */
    private $_addressFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Model\Customer
     */
    private $_customerModelMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Model\Attribute
     */
    private $_attributeModelMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Service\V1\CustomerMetadataServiceInterface
     */
    private $_eavMetadataServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Event\ManagerInterface
     */
    private $_eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Core\Model\StoreManagerInterface
     */
    private $_storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Math\Random
     */
    private $_mathRandomMock;

    /**
     * @var \Magento\Customer\Model\Converter
     */
    private $_converter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Core\Model\Store
     */
    private $_storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Service\V1\Dto\AddressBuilder
     */
    private $_addressBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Service\V1\Dto\CustomerBuilder
     */
    private $_customerBuilder;

    private $_validator;

    public function setUp()
    {
        $this->_customerFactoryMock = $this->getMockBuilder('Magento\Customer\Model\CustomerFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();

        $this->_customerModelMock = $this->getMockBuilder('Magento\Customer\Model\Customer')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'getId',
                    'getFirstname',
                    'getLastname',
                    'getName',
                    'getEmail',
                    'getAttributes',
                    'getConfirmation',
                    'setConfirmation',
                    'save',
                    'load',
                    '__wakeup',
                    'authenticate',
                    'getData',
                    'getDefaultBilling',
                    'getDefaultShipping',
                    'getDefaultShippingAddress',
                    'getDefaultBillingAddress',
                    'getStoreId',
                    'getAddressById',
                    'getAddresses',
                    'getAddressItemById',
                    'getParentId',
                    'isConfirmationRequired',
                    'addAddress',
                    'loadByEmail',
                    'sendNewAccountEmail',
                    'setFirstname',
                    'setLastname',
                    'setEmail',
                    'setPassword',
                    'setData',
                    'setWebsiteId',
                    'getAttributeSetId',
                    'setAttributeSetId',
                    'validate',
                    'getRpToken',
                    'setRpToken',
                    'setRpTokenCreatedAt',
                    'isResetPasswordLinkTokenExpired',
                    'changeResetPasswordLinkToken',
                    'sendPasswordResetConfirmationEmail',
                )
            )
            ->getMock();

        $this->_addressFactoryMock = $this->getMockBuilder('Magento\Customer\Model\AddressFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();

        $this->_eavMetadataServiceMock =
            $this->getMockBuilder('Magento\Customer\Service\Eav\AttributeMetadataServiceV1Interface')
                ->disableOriginalConstructor()
                ->getMock();

        $this->_eventManagerMock =
            $this->getMockBuilder('\Magento\Event\ManagerInterface')
                ->disableOriginalConstructor()
                ->getMock();

        $this->_attributeModelMock =
            $this->getMockBuilder('\Magento\Customer\Model\Attribute')
                ->disableOriginalConstructor()
                ->getMock();

        $this->_attributeModelMock
            ->expects($this->any())
            ->method('getAttributeCode')
            ->will($this->returnValue(self::ATTRIBUTE_CODE));

        $this->_customerModelMock
            ->expects($this->any())
            ->method('getData')
            ->with($this->equalTo(self::ATTRIBUTE_CODE))
            ->will($this->returnValue(self::ATTRIBUTE_VALUE));

        $this->_customerModelMock
            ->expects($this->any())
            ->method('validate')
            ->will($this->returnValue(TRUE));

        $this->_setupStoreMock();

        $this->_mathRandomMock = $this->getMockBuilder('\Magento\Math\Random')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_validator = $this->getMockBuilder('\Magento\Customer\Model\Metadata\Validator')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_addressBuilder = new Dto\AddressBuilder(
            new Dto\RegionBuilder());

        $this->_customerBuilder = new Dto\CustomerBuilder();

        $customerBuilder = new Dto\CustomerBuilder();

        $this->_converter = new \Magento\Customer\Model\Converter($customerBuilder, $this->_customerFactoryMock);
    }

    public function testGetAddressesDefaultBilling()
    {
        $addressMock = $this->_createAddress(1, 'John');
        $this->_customerModelMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($this->_customerModelMock));
        $this->_customerModelMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $this->_customerModelMock->expects($this->any())
            ->method('getDefaultBillingAddress')
            ->will($this->returnValue($addressMock));
        $this->_customerModelMock->expects($this->any())
            ->method('getDefaultBilling')
            ->will($this->returnValue(1));
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));

        $customerService = $this->_createService();

        $customerId = 1;
        $address = $customerService->getDefaultBillingAddress($customerId);

        $expected = [
            'id' => 1,
            'default_billing' => true,
            'default_shipping' => false,
            'customer_id' => self::ID,
            'region' => new Dto\Region([
                    'region_id' => self::REGION_ID,
                    'region_code' => '',
                    'region' => self::REGION
                ]),
            'country_id' => self::COUNTRY_ID,
            'street' => [self::STREET],
            'telephone' => self::TELEPHONE,
            'postcode' => self::POSTCODE,
            'city' => self::CITY,
            'firstname' => 'John',
            'lastname' => 'Doe',
        ];

        $this->assertEquals($expected, $address->__toArray());
    }

    public function testGetAddressesDefaultShipping()
    {
        $addressMock = $this->_createAddress(1, 'John');
        $this->_customerModelMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($this->_customerModelMock));
        $this->_customerModelMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $this->_customerModelMock->expects($this->any())
            ->method('getDefaultShippingAddress')
            ->will($this->returnValue($addressMock));
        $this->_customerModelMock->expects($this->any())
            ->method('getDefaultShipping')
            ->will($this->returnValue(1));
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));

        $customerService = $this->_createService();

        $customerId = 1;
        $address = $customerService->getDefaultShippingAddress($customerId);

        $expected = [
            'id' => 1,
            'default_shipping' => true,
            'default_billing' => false,
            'customer_id' => self::ID,
            'region' => new Dto\Region([
                    'region_id' => self::REGION_ID,
                    'region_code' => '',
                    'region' => self::REGION
                ]),
            'country_id' => self::COUNTRY_ID,
            'street' => [self::STREET],
            'telephone' => self::TELEPHONE,
            'postcode' => self::POSTCODE,
            'city' => self::CITY,
            'firstname' => 'John',
            'lastname' => 'Doe',
        ];

        $this->assertEquals($expected, $address->__toArray());
    }

    public function testGetAddressesById()
    {
        $addressMock = $this->_createAddress(1, 'John');
        $this->_customerModelMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($this->_customerModelMock));
        $this->_customerModelMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $this->_customerModelMock->expects($this->any())
            ->method('getAddressById')
            ->will($this->returnValue($addressMock));
        $this->_customerModelMock->expects($this->any())
            ->method('getDefaultShipping')
            ->will($this->returnValue(1));
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));

        $customerService = $this->_createService();

        $customerId = 1;
        $addressId = 1;
        $address = $customerService->getAddressById($customerId, $addressId);

        $expected = [
            'id' => 1,
            'default_shipping' => true,
            'default_billing' => false,
            'customer_id' => self::ID,
            'region' => new Dto\Region([
                    'region_id' => self::REGION_ID,
                    'region_code' => '',
                    'region' => self::REGION
                ]),
            'country_id' => self::COUNTRY_ID,
            'street' => [self::STREET],
            'telephone' => self::TELEPHONE,
            'postcode' => self::POSTCODE,
            'city' => self::CITY,
            'firstname' => 'John',
            'lastname' => 'Doe',
        ];

        $this->assertEquals($expected, $address->__toArray());
    }

    public function testGetAddresses()
    {
        $addressMock = $this->_createAddress(1, 'John');
        $addressMock2 = $this->_createAddress(2, 'Genry');
        $this->_customerModelMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($this->_customerModelMock));
        $this->_customerModelMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $this->_customerModelMock->expects($this->any())
            ->method('getAddresses')
            ->will($this->returnValue([$addressMock, $addressMock2]));
        $this->_customerModelMock->expects($this->any())
            ->method('getDefaultShipping')
            ->will($this->returnValue(1));
        $this->_customerModelMock->expects($this->any())
            ->method('getDefaultBilling')
            ->will($this->returnValue(2));
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));

        $customerService = $this->_createService();

        $addresses = $customerService->getAddresses(1);

        $expected = [
            [
                'id' => 1,
                'default_shipping' => true,
                'default_billing' => false,
                'customer_id' => self::ID,
                'region' => new Dto\Region([
                        'region_id' => self::REGION_ID,
                        'region_code' => '',
                        'region' => self::REGION
                    ]),
                'country_id' => self::COUNTRY_ID,
                'street' => [self::STREET],
                'telephone' => self::TELEPHONE,
                'postcode' => self::POSTCODE,
                'city' => self::CITY,
                'firstname' => 'John',
                'lastname' => 'Doe',
            ], [
                'id' => 2,
                'default_billing' => true,
                'default_shipping' => false,
                'customer_id' => self::ID,
                'region' => new Dto\Region([
                        'region_id' => self::REGION_ID,
                        'region_code' => '',
                        'region' => self::REGION
                    ]),
                'country_id' => self::COUNTRY_ID,
                'street' => [self::STREET],
                'telephone' => self::TELEPHONE,
                'postcode' => self::POSTCODE,
                'city' => self::CITY,
                'firstname' => 'Genry',
                'lastname' => 'Doe',
            ]
        ];

        $this->assertEquals($expected[0], $addresses[0]->__toArray());
        $this->assertEquals($expected[1], $addresses[1]->__toArray());
    }

    public function testSaveAddresses()
    {
        // Setup Customer mock
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));
        $this->_customerModelMock->expects($this->any())
            ->method('load')
            ->will($this->returnSelf());
        $this->_customerModelMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $this->_customerModelMock->expects($this->any())
            ->method('getAddresses')
            ->will($this->returnValue([]));

        // Setup address mock
        $mockAddress = $this->_createAddress(1, 'John');
        $mockAddress->expects($this->once())
            ->method('save');
        $mockAddress->expects($this->any())
            ->method('setData');
        $this->_addressFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($mockAddress));
        $customerService = $this->_createService();

        $this->_addressBuilder->setFirstname('John')
            ->setLastname(self::LASTNAME)
            ->setRegion(new Dto\Region([
                'region_id' => self::REGION_ID,
                'region_code' => '',
                'region' => self::REGION
            ]))
            ->setStreet([self::STREET])
            ->setTelephone(self::TELEPHONE)
            ->setCity(self::CITY)
            ->setCountryId(self::COUNTRY_ID)
            ->setPostcode(self::POSTCODE);
        $ids = $customerService->saveAddresses(1, [$this->_addressBuilder->create()]);
        $this->assertEquals([1], $ids);
    }

    public function testSaveAddressesChanges()
    {
        // Setup address mock
        $mockAddress = $this->_createAddress(1, 'John');

        // Setup Customer mock
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));
        $this->_customerModelMock->expects($this->any())
            ->method('load')
            ->will($this->returnSelf());
        $this->_customerModelMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $this->_customerModelMock->expects($this->any())
            ->method('getAddressItemById')
            ->with(1)
            ->will($this->returnValue($mockAddress));

        // Assert
        $mockAddress->expects($this->once())
            ->method('save');
        $mockAddress->expects($this->any())
            ->method('setData');

        $customerService = $this->_createService();
        $this->_addressBuilder->setId(1)
            ->setFirstname('Jane')
            ->setLastname(self::LASTNAME)
            ->setRegion(new Dto\Region([
                'region_id' => self::REGION_ID,
                'region_code' => '',
                'region' => self::REGION
            ]))
            ->setStreet([self::STREET])
            ->setTelephone(self::TELEPHONE)
            ->setCity(self::CITY)
            ->setCountryId(self::COUNTRY_ID)
            ->setPostcode(self::POSTCODE);
        $ids = $customerService->saveAddresses(1, [$this->_addressBuilder->create()]);
        $this->assertEquals([1], $ids);
    }

    public function testSaveAddressesNoAddresses()
    {
        // Setup Customer mock
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));
        $this->_customerModelMock->expects($this->any())
            ->method('load')
            ->will($this->returnSelf());
        $this->_customerModelMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $customerService = $this->_createService();

        $ids = $customerService->saveAddresses(1, []);
        $this->assertEmpty($ids);
    }

    public function testSaveAddressesIdSetButNotAlreadyExisting()
    {
        // Setup Customer mock
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));
        $this->_customerModelMock->expects($this->any())
            ->method('load')
            ->will($this->returnSelf());
        $this->_customerModelMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $this->_customerModelMock->expects($this->any())
            ->method('getAddresses')
            ->will($this->returnValue([]));
        $this->_customerModelMock->expects($this->any())
            ->method('getAddressItemById')
            ->with(1)
            ->will($this->returnValue(null));

        // Setup address mock
        $mockAddress = $this->_createAddress(1, 'John');
        $mockAddress->expects($this->once())
            ->method('save');
        $mockAddress->expects($this->any())
            ->method('setData');
        $this->_addressFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($mockAddress));
        $customerService = $this->_createService();

        $this->_addressBuilder->setId(1)
            ->setFirstname('John')
            ->setLastname(self::LASTNAME)
            ->setRegion(new Dto\Region([
                'region_id' => self::REGION_ID,
                'region_code' => '',
                'region' => self::REGION
            ]))
            ->setStreet([self::STREET])
            ->setTelephone(self::TELEPHONE)
            ->setCity(self::CITY)
            ->setCountryId(self::COUNTRY_ID)
            ->setPostcode(self::POSTCODE);
        $ids = $customerService->saveAddresses(1, [$this->_addressBuilder->create()]);
        $this->assertEquals([1], $ids);
    }

    /**
     * @expectedException \Magento\Customer\Service\Entity\V1\Exception
     * @expectedExceptionMessage No customer with customerId 4200 exists
     */
    public function testSaveAddressesCustomerIdNotExist()
    {
        // Setup Customer mock
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));
        $this->_customerModelMock->expects($this->any())
            ->method('load')
            ->will($this->returnSelf());
        $this->_customerModelMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(0));
        $this->_customerModelMock->expects($this->any())
            ->method('getAddresses')
            ->will($this->returnValue([]));
        $this->_customerModelMock->expects($this->any())
            ->method('getAddressItemById')
            ->with(1)
            ->will($this->returnValue(null));
        $customerService = $this->_createService();
        $this->_addressBuilder->setFirstname('John')
            ->setLastname(self::LASTNAME)
            ->setRegion(new Dto\Region([
                'region_id' => self::REGION_ID,
                'region_code' => '',
                'region' => self::REGION
            ]))
            ->setStreet([self::STREET])
            ->setTelephone(self::TELEPHONE)
            ->setCity(self::CITY)
            ->setCountryId(self::COUNTRY_ID)
            ->setPostcode(self::POSTCODE);

        $failures = $customerService->saveAddresses(4200, [$this->_addressBuilder->create()]);
        $this->assertEmpty($failures);
    }

    public function testDeleteAddressFromCustomer()
    {
        // Setup address mock
        $mockAddress = $this->_createAddress(1, 'John');
        $mockAddress->expects($this->any())
            ->method('getCustomerId')
            ->will($this->returnValue(self::ID));
        $this->_addressFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($mockAddress));

        // verify delete is called on the mock address model
        $mockAddress->expects($this->once())
            ->method('delete');

        $customerService = $this->_createService();
        $customerService->deleteAddressFromCustomer(1, 1);
    }

    /**
     * @expectedException \Magento\Customer\Service\Entity\V1\Exception
     * @expectedExceptionCode \Magento\Customer\Service\Entity\V1\Exception::CODE_CUSTOMER_ID_MISMATCH
     */
    public function testDeleteAddressFromCustomerMismatch()
    {
        // Setup address mock
        $mockAddress = $this->_createAddress(1, 'John', 55);
        $this->_addressFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($mockAddress));

        // verify delete is called on the mock address model
        $mockAddress->expects($this->never())
            ->method('delete');

        $customerService = $this->_createService();
        $customerService->deleteAddressFromCustomer(1, 1);
    }

    /**
     * @expectedException \Magento\Customer\Service\Entity\V1\Exception
     * @expectedExceptionCode \Magento\Customer\Service\Entity\V1\Exception::CODE_ADDRESS_NOT_FOUND
     */
    public function testDeleteAddressFromCustomerBadAddrId()
    {
        // Setup address mock
        $mockAddress = $this->_createAddress(0, '');
        $mockAddress->expects($this->any())
            ->method('getCustomerId')
            ->will($this->returnValue(self::ID));
        $this->_addressFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($mockAddress));

        // verify delete is called on the mock address model
        $mockAddress->expects($this->never())
            ->method('delete');

        $customerService = $this->_createService();
        $customerService->deleteAddressFromCustomer(1, 2);
    }

    /**
     * @expectedException \Magento\Customer\Service\Entity\V1\Exception
     * @expectedExceptionCode \Magento\Customer\Service\Entity\V1\Exception::CODE_INVALID_ADDRESS_ID
     */
    public function testDeleteAddressFromCustomerInvalidAddrId()
    {
        $customerService = $this->_createService();
        $customerService->deleteAddressFromCustomer(1, 0);
    }


    public function testSaveAddressesWithValidatorException()
    {
        // Setup Customer mock
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));
        $this->_customerModelMock->expects($this->any())
            ->method('load')
            ->will($this->returnSelf());
        $this->_customerModelMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $this->_customerModelMock->expects($this->any())
            ->method('getAddresses')
            ->will($this->returnValue([]));

        // Setup address mock
        $mockAddress = $this->getMockBuilder('Magento\Customer\Model\Address')
            ->disableOriginalConstructor()
            ->getMock();
        $mockAddress->expects($this->any())
            ->method('validate')
            ->will($this->returnValue(['some error']));
        $this->_addressFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($mockAddress));
        $customerService = $this->_createService();

        $this->_addressBuilder->setFirstname('John')
            ->setLastname(self::LASTNAME)
            ->setRegion(new Dto\Region([
                'region_id' => self::REGION_ID,
                'region_code' => '',
                'region' => self::REGION
            ]))
            ->setStreet([self::STREET])
            ->setTelephone(self::TELEPHONE)
            ->setCity(self::CITY)
            ->setCountryId(self::COUNTRY_ID)
            ->setPostcode(self::POSTCODE);
        try {
            $customerService->saveAddresses(1, [$this->_addressBuilder->create()]);
        } catch (\Magento\Customer\Service\Entity\V1\AggregateException $ae) {
            $addressException = $ae->getExceptions()[0];
            $this->assertInstanceOf('\Magento\Customer\Service\Entity\V1\Exception', $addressException);
            $this->assertInstanceOf('\Magento\Validator\ValidatorException', $addressException->getPrevious());
            $this->assertSame('some error', $addressException->getPrevious()->getMessage());
            return;
        }
        $this->fail("Expected AggregateException not caught.");
    }


    private function _setupStoreMock()
    {
        $this->_storeManagerMock =
            $this->getMockBuilder('\Magento\Core\Model\StoreManagerInterface')
                ->disableOriginalConstructor()
                ->getMock();

        $this->_storeMock = $this->getMockBuilder('\Magento\Core\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_storeManagerMock
            ->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->_storeMock));
    }

    /**
     * @return CustomerAddressService
     */
    private function _createService()
    {
        $customerService = new CustomerAddressService(
            $this->_addressFactoryMock,
            $this->_converter,
            new Dto\RegionBuilder(),
            $this->_addressBuilder
        );
        return $customerService;
    }


    /**
     * Helper that returns a mock \Magento\Customer\Model\Address object.
     *
     * @param $addrId
     * @param $firstName
     * @param $customerId
     * @return \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Model\Address
     */
    private function _createAddress($addrId, $firstName, $customerId = self::ID)
    {
        $attributes = [
            $this->_createAttribute('firstname'),
            $this->_createAttribute('lastname'),
            $this->_createAttribute('street'),
            $this->_createAttribute('city'),
            $this->_createAttribute('postcode'),
            $this->_createAttribute('telephone'),
            $this->_createAttribute('region_id'),
            $this->_createAttribute('region'),
            $this->_createAttribute('country_id'),
        ];

        $addressMock = $this->getMockBuilder('Magento\Customer\Model\Address')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getId', 'hasDataChanges', 'getRegion', 'getRegionId',
                    'addData', 'setData', 'setCustomerId', 'setPostIndex',
                    'setFirstname', 'load', 'save', '__sleep', '__wakeup',
                    'getDefaultAttributeCodes', 'getAttributes', 'getData',
                    'getCustomerId', 'getParentId', 'delete', 'validate'
                ]
            )
            ->getMock();
        $addressMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($addrId));
        $addressMock->expects($this->any())
            ->method('getRegion')
            ->will($this->returnValue(self::REGION));
        $addressMock->expects($this->any())
            ->method('getRegionId')
            ->will($this->returnValue(self::REGION_ID));
        $addressMock->expects($this->any())
            ->method('getCustomerId')
            ->will($this->returnValue($customerId));
        $addressMock->expects($this->any())
            ->method('validate')
            ->will($this->returnValue(true));

        $map = [
            ['firstname', null, $firstName],
            ['lastname', null, self::LASTNAME],
            ['street', null, self::STREET],
            ['city', null, self::CITY],
            ['postcode', null, self::POSTCODE],
            ['telephone', null, self::TELEPHONE],
            ['region', null, self::REGION],
            ['country_id', null, self::COUNTRY_ID],
        ];

        $addressMock->expects($this->any())
            ->method('getData')
            ->will($this->returnValueMap($map));

        $addressMock->expects($this->any())
            ->method('load')
            ->will($this->returnSelf());
        $addressMock->expects($this->any())
            ->method('getDefaultAttributeCodes')
            ->will($this->returnValue(['entity_id', 'attribute_set_id']));
        $addressMock->expects($this->any())
            ->method('getAttributes')
            ->will($this->returnValue($attributes));
        return $addressMock;
    }

    private function _createAttribute($attributeCode)
    {
        $attribute = $this->getMockBuilder('\Magento\Customer\Model\Attribute')
            ->disableOriginalConstructor()
            ->getMock();
        $attribute->expects($this->any())
            ->method('getAttributeCode')
            ->will($this->returnValue($attributeCode));
        return $attribute;
    }

}
