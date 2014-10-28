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

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Service\V1\Data\RegionBuilder;
use Magento\Customer\Service\V1\Data\CustomerBuilder;
use Magento\Framework\Service\Data\AttributeValueBuilder;

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
    /** Sample values for testing */
    const STREET = 'Parmer';

    const CITY = 'Albuquerque';

    const POSTCODE = '90014';

    const TELEPHONE = '7143556767';

    const REGION = 'Alabama';

    const REGION_ID = 1;

    const COUNTRY_ID = 'US';

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
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Model\AddressRegistry
     */
    private $_addressRegistryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Model\CustomerRegistry
     */
    private $_customerRegistryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Model\Customer
     */
    private $_customerModelMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\StoreManagerInterface
     */
    private $_storeManagerMock;

    /**
     * @var \Magento\Customer\Model\Converter
     */
    private $_customerConverter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Store\Model\Store
     */
    private $_storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Model\Address\Converter
     */
    private $_addressConverterMock;

    /**
     * @var \Magento\Customer\Service\V1\Data\AddressBuilder
     */
    private $_addressBuilder;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    private $_directoryData;

    /**
     * @var \Magento\Customer\Model\Metadata\Validator
     */
    private $_validator;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    public function setUp()
    {
        $this->_customerFactoryMock = $this->getMockBuilder(
            'Magento\Customer\Model\CustomerFactory'
        )->disableOriginalConstructor()->setMethods(
            array('create')
        )->getMock();

        $this->_customerModelMock = $this->getMockBuilder(
            'Magento\Customer\Model\Customer'
        )->disableOriginalConstructor()->setMethods(
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
                'sendPasswordResetConfirmationEmail'
            )
        )->getMock();

        $this->_addressRegistryMock = $this->getMockBuilder('Magento\Customer\Model\AddressRegistry')
            ->disableOriginalConstructor()
            ->setMethods(array('retrieve', 'remove'))
            ->getMock();

        $this->_customerRegistryMock = $this->getMockBuilder('Magento\Customer\Model\CustomerRegistry')
            ->disableOriginalConstructor()
            ->setMethods(array('retrieve', 'remove'))
            ->getMock();

        $this->_directoryData = $this->getMockBuilder(
            '\Magento\Directory\Helper\Data'
        )->disableOriginalConstructor()->setMethods(
            array('getCountriesWithOptionalZip')
        )->getMock();

        $this->_directoryData->expects(
            $this->any()
        )->method(
            'getCountriesWithOptionalZip'
        )->will(
            $this->returnValue(array())
        );

        $this->_customerModelMock->expects(
            $this->any()
        )->method(
            'getData'
        )->with(
            $this->equalTo(self::ATTRIBUTE_CODE)
        )->will(
            $this->returnValue(self::ATTRIBUTE_VALUE)
        );

        $this->_customerModelMock->expects($this->any())->method('validate')->will($this->returnValue(true));

        $this->_setupStoreMock();

        $this->_validator = $this->getMockBuilder(
            '\Magento\Customer\Model\Metadata\Validator'
        )->disableOriginalConstructor()->getMock();

        $this->objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $regionBuilder = $this->objectManagerHelper->getObject('Magento\Customer\Service\V1\Data\RegionBuilder');

        $customerMetadataService = $this->getMockForAbstractClass(
            'Magento\Customer\Service\V1\CustomerMetadataServiceInterface',
            array(),
            '',
            false
        );

        $addressMetadataService = $this->getMockForAbstractClass(
            'Magento\Customer\Service\V1\AddressMetadataServiceInterface',
            array(),
            '',
            false
        );

        $addressMetadataService->expects($this->any())
            ->method('getCustomAttributesMetadata')
            ->will($this->returnValue(array()));

        $customerMetadataService->expects(
            $this->any()
        )->method(
            'getCustomAttributesMetadata'
        )->will(
            $this->returnValue(array())
        );

        $this->_addressBuilder = $this->objectManagerHelper->getObject(
            'Magento\Customer\Service\V1\Data\AddressBuilder',
            array('regionBuilder' => $regionBuilder, 'metadataService' => $addressMetadataService)
        );

        $customerBuilder = $this->objectManagerHelper->getObject(
            'Magento\Customer\Service\V1\Data\CustomerBuilder',
            ['metadataService' => $customerMetadataService]
        );

        $this->_customerConverter = new \Magento\Customer\Model\Converter(
            $customerBuilder,
            $this->_customerFactoryMock,
            $this->_storeManagerMock
        );

        $this->_addressConverterMock = $this->getMockBuilder(
            '\Magento\Customer\Model\Address\Converter'
        )->disableOriginalConstructor()->getMock();
    }

    public function testGetAddressesDefaultBilling()
    {
        $customerId = 1;

        $addressMock = $this->_createAddress(1, 'John');
        $this->_customerModelMock->expects(
            $this->any()
        )->method(
            'load'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );
        $this->_customerModelMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->_customerModelMock->expects(
            $this->any()
        )->method(
            'getDefaultBillingAddress'
        )->will(
            $this->returnValue($addressMock)
        );
        $this->_customerModelMock->expects($this->any())->method('getDefaultBilling')->will($this->returnValue(1));
        $this->_customerModelMock->expects($this->any())->method('getDefaultShipping')->will($this->returnValue(0));
        $this->_customerRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with($customerId)
            ->will($this->returnValue($this->_customerModelMock));

        $this->_addressConverterMock->expects(
            $this->once()
        )->method(
            'createAddressFromModel'
        )->with(
            $addressMock,
            1,
            0
        )->will(
            $this->returnValue('address')
        );

        $customerService = $this->_createService();

        $address = $customerService->getDefaultBillingAddress($customerId);

        $this->assertEquals('address', $address);
    }

    public function testGetAddressesDefaultShipping()
    {
        $customerId = 1;

        $addressMock = $this->_createAddress(1, 'John');
        $this->_customerModelMock->expects(
            $this->any()
        )->method(
            'load'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );
        $this->_customerModelMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->_customerModelMock->expects(
            $this->any()
        )->method(
            'getDefaultShippingAddress'
        )->will(
            $this->returnValue($addressMock)
        );
        $this->_customerModelMock->expects($this->any())->method('getDefaultShipping')->will($this->returnValue(1));
        $this->_customerModelMock->expects($this->any())->method('getDefaultBilling')->will($this->returnValue(0));
        $this->_customerRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with($customerId)
            ->will($this->returnValue($this->_customerModelMock));
        $this->_addressConverterMock->expects($this->once())
            ->method('createAddressFromModel')
            ->with($addressMock, 0, 1)
            ->will($this->returnValue('address'));

        $customerService = $this->_createService();

        $customerId = 1;
        $address = $customerService->getDefaultShippingAddress($customerId);

        $this->assertEquals('address', $address);
    }

    public function testGetAddressById()
    {
        $addressMock = $this->_createAddress(1, 'John');
        $addressMock->expects($this->any())->method('getCustomerId')->will($this->returnValue(self::ID));
        $this->_addressRegistryMock->expects($this->once())
            ->method('retrieve')
            ->will($this->returnValue($addressMock));
        $this->_customerModelMock->expects(
            $this->any()
        )->method(
            'load'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );
        $this->_customerModelMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->_customerModelMock->expects($this->any())->method('getDefaultShipping')->will($this->returnValue(1));
        $this->_customerModelMock->expects($this->any())->method('getDefaultBilling')->will($this->returnValue(0));
        $this->_customerRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with(self::ID)
            ->will($this->returnValue($this->_customerModelMock));
        $this->_addressConverterMock->expects(
            $this->once()
        )->method(
            'createAddressFromModel'
        )->with(
            $addressMock,
            0,
            1
        )->will(
            $this->returnValue('address')
        );

        $customerService = $this->_createService();

        $addressId = 1;
        $address = $customerService->getAddress($addressId);
        $this->assertEquals('address', $address);
    }

    public function testGetAddresses()
    {
        $customerId = 1;

        $addressMock = $this->_createAddress(1, 'John');
        $addressMock2 = $this->_createAddress(2, 'Genry');
        $this->_customerModelMock->expects(
            $this->any()
        )->method(
            'load'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );
        $this->_customerModelMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($customerId));
        $this->_customerModelMock->expects(
            $this->any()
        )->method(
            'getAddresses'
        )->will(
            $this->returnValue(array($addressMock, $addressMock2))
        );
        $this->_customerModelMock->expects($this->any())->method('getDefaultShipping')->will($this->returnValue(1));
        $this->_customerModelMock->expects($this->any())->method('getDefaultBilling')->will($this->returnValue(2));
        $this->_customerRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with($customerId)
            ->will($this->returnValue($this->_customerModelMock));

        $this->_addressConverterMock->expects(
            $this->at(0)
        )->method(
            'createAddressFromModel'
        )->with(
            $addressMock,
            2,
            1
        )->will(
            $this->returnValue('address')
        );

        $this->_addressConverterMock->expects(
            $this->at(1)
        )->method(
            'createAddressFromModel'
        )->with(
            $addressMock2,
            2,
            1
        )->will(
            $this->returnValue('address2')
        );

        $customerService = $this->_createService();
        $addresses = $customerService->getAddresses(1);

        $this->assertEquals(array('address', 'address2'), $addresses);
    }

    public function testSaveAddresses()
    {
        // Setup Customer mock
        $this->_customerRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with(1)
            ->will($this->returnValue($this->_customerModelMock));
        $this->_customerModelMock->expects($this->any())->method('load')->will($this->returnSelf());
        $this->_customerModelMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->_customerModelMock->expects($this->any())->method('getAddresses')->will($this->returnValue(array()));

        // Setup address mock
        $mockAddress = $this->_createAddress(1, 'John');
        $mockAddress->expects($this->once())
            ->method('save');
        $mockAddress->expects($this->any())
            ->method('setData');
        $mockAddress->expects($this->any())
            ->method('setCustomer');
        $this->_addressConverterMock->expects($this->once())
            ->method('createAddressModel')
            ->will($this->returnValue($mockAddress));

        $customerService = $this->_createService();


        $this->_addressBuilder->setFirstname(
            'John'
        )->setLastname(
            self::LASTNAME
        )->setRegion(
            $this->objectManagerHelper->getObject('\Magento\Customer\Service\V1\Data\RegionBuilder')
                ->setRegionId(self::REGION_ID)->setRegion(self::REGION)->create()
        )->setStreet(
            array(self::STREET)
        )->setTelephone(
            self::TELEPHONE
        )->setCity(
            self::CITY
        )->setCountryId(
            self::COUNTRY_ID
        )->setPostcode(
            self::POSTCODE
        );
        $ids = $customerService->saveAddresses(1, array($this->_addressBuilder->create()));
        $this->assertEquals(array(1), $ids);
    }

    public function testSaveAddressesChanges()
    {
        // Setup address mock
        $mockAddress = $this->_createAddress(1, 'John');

        // Setup Customer mock
        $this->_customerRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with(1)
            ->will($this->returnValue($this->_customerModelMock));
        $this->_customerModelMock->expects($this->any())->method('load')->will($this->returnSelf());
        $this->_customerModelMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->_customerModelMock->expects(
            $this->any()
        )->method(
            'getAddressItemById'
        )->with(
            1
        )->will(
            $this->returnValue($mockAddress)
        );

        // Assert
        $mockAddress->expects($this->once())->method('save');
        $mockAddress->expects($this->any())->method('setData');

        $customerService = $this->_createService();
        $this->_addressBuilder->setId(
            1
        )->setFirstname(
            'Jane'
        )->setLastname(
            self::LASTNAME
        )->setRegion(
                $this->objectManagerHelper->getObject('\Magento\Customer\Service\V1\Data\RegionBuilder')
                    ->setRegionId(self::REGION_ID)->setRegion(self::REGION)->create()
        )->setStreet(
            array(self::STREET)
        )->setTelephone(
            self::TELEPHONE
        )->setCity(
            self::CITY
        )->setCountryId(
            self::COUNTRY_ID
        )->setPostcode(
            self::POSTCODE
        );
        $ids = $customerService->saveAddresses(1, array($this->_addressBuilder->create()));
        $this->assertEquals(array(1), $ids);
    }

    public function testSaveAddressesNoAddresses()
    {
        // Setup Customer mock
        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );
        $this->_customerModelMock->expects($this->any())->method('load')->will($this->returnSelf());
        $this->_customerModelMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $customerService = $this->_createService();

        $ids = $customerService->saveAddresses(1, array());
        $this->assertEmpty($ids);
    }

    public function testSaveAddressesIdSetButNotAlreadyExisting()
    {
        // Setup Customer mock
        $this->_customerRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with(1)
            ->will($this->returnValue($this->_customerModelMock));
        $this->_customerModelMock->expects($this->any())->method('load')->will($this->returnSelf());
        $this->_customerModelMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->_customerModelMock->expects($this->any())->method('getAddresses')->will($this->returnValue(array()));
        $this->_customerModelMock->expects(
            $this->any()
        )->method(
            'getAddressItemById'
        )->with(
            1
        )->will(
            $this->returnValue(null)
        );

        // Setup address mock
        $mockAddress = $this->_createAddress(1, 'John');
        $mockAddress->expects($this->once())->method('save');
        $mockAddress->expects($this->any())->method('setData');
        $this->_addressConverterMock->expects($this->once())
            ->method('createAddressModel')
            ->will($this->returnValue($mockAddress));
        $customerService = $this->_createService();

        $this->_addressBuilder->setId(
            1
        )->setFirstname(
            'John'
        )->setLastname(
            self::LASTNAME
        )->setRegion(
                $this->objectManagerHelper->getObject('\Magento\Customer\Service\V1\Data\RegionBuilder')
                    ->setRegionId(self::REGION_ID)->setRegion(self::REGION)->create()
        )->setStreet(
            array(self::STREET)
        )->setTelephone(
            self::TELEPHONE
        )->setCity(
            self::CITY
        )->setCountryId(
            self::COUNTRY_ID
        )->setPostcode(
            self::POSTCODE
        );
        $ids = $customerService->saveAddresses(1, array($this->_addressBuilder->create()));
        $this->assertEquals(array(1), $ids);
    }

    public function testSaveAddressesCustomerIdNotExist()
    {
        $expectedException = NoSuchEntityException::singleField('customerId', 4200);

        // Setup Customer mock
        $this->_customerRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with(4200)
            ->will($this->throwException($expectedException));
        $this->_customerModelMock->expects($this->any())->method('load')->will($this->returnSelf());
        $this->_customerModelMock->expects($this->any())->method('getId')->will($this->returnValue(0));
        $this->_customerModelMock->expects($this->any())->method('getAddresses')->will($this->returnValue(array()));
        $this->_customerModelMock->expects(
            $this->any()
        )->method(
            'getAddressItemById'
        )->with(
            1
        )->will(
            $this->returnValue(null)
        );
        $customerService = $this->_createService();
        $this->_addressBuilder->setFirstname(
            'John'
        )->setLastname(
            self::LASTNAME
        )->setRegion(
            $this->objectManagerHelper->getObject('\Magento\Customer\Service\V1\Data\RegionBuilder')
                ->setRegionId(self::REGION_ID)->setRegion(self::REGION)->create()
        )->setStreet(
            array(self::STREET)
        )->setTelephone(
            self::TELEPHONE
        )->setCity(
            self::CITY
        )->setCountryId(
            self::COUNTRY_ID
        )->setPostcode(
            self::POSTCODE
        );

        try {
            $customerService->saveAddresses(4200, array($this->_addressBuilder->create()));
            $this->fail("Expected NoSuchEntityException not caught");
        } catch (NoSuchEntityException $e) {
            $this->assertSame($e, $expectedException);
        }
    }

    public function testDeleteAddressFromCustomer()
    {
        // Setup address mock
        $mockAddress = $this->_createAddress(1, 'John');
        $mockAddress->expects($this->any())->method('getCustomerId')->will($this->returnValue(self::ID));
        $this->_addressRegistryMock->expects($this->once())
            ->method('retrieve')
            ->will($this->returnValue($mockAddress));

        // verify delete is called on the mock address model
        $mockAddress->expects($this->once())->method('delete');

        $customerService = $this->_createService();
        $customerService->deleteAddress(1);
    }

    public function testDeleteAddressFromCustomerBadAddrId()
    {
        // Setup address mock
        $mockAddress = $this->_createAddress(0, '');
        $mockAddress->expects($this->any())->method('getCustomerId')->will($this->returnValue(self::ID));
        $this->_addressRegistryMock->expects($this->once())
            ->method('retrieve')
            ->will($this->throwException(NoSuchEntityException::singleField('addressId', 2)));

        // verify delete is called on the mock address model
        $mockAddress->expects($this->never())->method('delete');

        $customerService = $this->_createService();
        try {
            $customerService->deleteAddress(2);
            $this->fail("Expected NoSuchEntityException not caught");
        } catch (NoSuchEntityException $exception) {
            $this->assertSame('No such entity with addressId = 2', $exception->getMessage());
        }
    }

    public function testSaveAddressesWithValidatorException()
    {
        // Setup Customer mock
        $this->_customerRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with(1)
            ->will($this->returnValue($this->_customerModelMock));
        $this->_customerModelMock->expects($this->any())->method('load')->will($this->returnSelf());
        $this->_customerModelMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->_customerModelMock->expects($this->any())->method('getAddresses')->will($this->returnValue(array()));

        // Setup address mock, no first name
        $mockAddress = $this->_createAddress(1, '');
        $this->_addressConverterMock->expects($this->once())
            ->method('createAddressModel')
            ->will($this->returnValue($mockAddress));
        $customerService = $this->_createService();

        $this->_addressBuilder->setFirstname(
            'John'
        )->setLastname(
            self::LASTNAME
        )->setRegion(
            $this->objectManagerHelper->getObject('\Magento\Customer\Service\V1\Data\RegionBuilder')
                ->setRegionId(self::REGION_ID)->setRegion(self::REGION)->create()
        )->setStreet(
            array(self::STREET)
        )->setTelephone(
            self::TELEPHONE
        )->setCity(
            self::CITY
        )->setCountryId(
            self::COUNTRY_ID
        )->setPostcode(
            self::POSTCODE
        );
        try {
            $customerService->saveAddresses(1, array($this->_addressBuilder->create()));
            $this->fail("Expected InputException not caught");
        } catch (InputException $inputException) {
            $this->assertEquals(InputException::REQUIRED_FIELD, $inputException->getRawMessage());
            $this->assertEquals('firstname is a required field.', $inputException->getMessage());
            $this->assertEquals('firstname is a required field.', $inputException->getLogMessage());
            $this->assertTrue($inputException->wasErrorAdded());
            $this->assertEmpty($inputException->getErrors());
        }
    }

    public function testValidateAddressesEmpty()
    {
        $customerService = $this->_createService();
        $this->assertTrue($customerService->validateAddresses(array()));
    }

    public function testValidateAddressesValid()
    {
        // Setup address mock
        $mockAddress = $this->_createAddress(1, 'John');

        $address = $this->_addressBuilder->setFirstname(
            'John'
        )->setLastname(
            self::LASTNAME
        )->setRegion(
            $this->objectManagerHelper->getObject('\Magento\Customer\Service\V1\Data\RegionBuilder')
                ->setRegionId(self::REGION_ID)->setRegion(self::REGION)->create()
        )->setStreet(
            array(self::STREET)
        )->setTelephone(
            self::TELEPHONE
        )->setCity(
            self::CITY
        )->setCountryId(
            self::COUNTRY_ID
        )->setPostcode(
            self::POSTCODE
        )->create();

        $this->_addressConverterMock->expects(
            $this->once()
        )->method(
            'createAddressModel'
        )->with(
            $address
        )->will(
            $this->returnValue($mockAddress)
        );

        $customerService = $this->_createService();

        $this->assertTrue($customerService->validateAddresses(array($address)));
    }

    public function testValidateAddressesBoth()
    {
        // Setup address mock, no first name
        $mockBadAddress = $this->_createAddress(1, '');

        // Setup address mock, with first name
        $mockAddress = $this->_createAddress(1, 'John');

        $addressBad = $this->_addressBuilder->create();
        $addressGood = $this->_addressBuilder->create();

        $this->_addressConverterMock->expects(
            $this->any()
        )->method(
            'createAddressModel'
        )->will(
            $this->returnValueMap(array(array($addressBad, $mockBadAddress), array($addressGood, $mockAddress)))
        );
        $customerService = $this->_createService();

        try {
            $customerService->validateAddresses(array($addressBad, $addressGood));
            $this->fail("InputException was expected but not thrown");
        } catch (InputException $actualException) {
            $expectedException = new InputException();
            $expectedException->addError(InputException::REQUIRED_FIELD, ['fieldName' => 'firstname', 'index' => 0]);
            $this->assertEquals($expectedException, $actualException);
        }
    }

    private function _setupStoreMock()
    {
        $this->_storeManagerMock = $this->getMockBuilder(
            '\Magento\Framework\StoreManagerInterface'
        )->disableOriginalConstructor()->getMock();

        $this->_storeMock = $this->getMockBuilder(
            '\Magento\Store\Model\Store'
        )->disableOriginalConstructor()->getMock();

        $this->_storeManagerMock->expects(
            $this->any()
        )->method(
            'getStore'
        )->will(
            $this->returnValue($this->_storeMock)
        );
    }

    /**
     * @return CustomerAddressService
     */
    private function _createService()
    {
        $customerService = new CustomerAddressService(
            $this->_addressRegistryMock,
            $this->_addressConverterMock,
            $this->_customerRegistryMock,
            $this->_directoryData
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
        $attributes = array(
            $this->_createAttribute('firstname'),
            $this->_createAttribute('lastname'),
            $this->_createAttribute('street'),
            $this->_createAttribute('city'),
            $this->_createAttribute('postcode'),
            $this->_createAttribute('telephone'),
            $this->_createAttribute('region_id'),
            $this->_createAttribute('region'),
            $this->_createAttribute('country_id')
        );

        $addressMock = $this->getMockBuilder(
            'Magento\Customer\Model\Address'
        )->disableOriginalConstructor()->setMethods(
            array(
                'getId',
                'hasDataChanges',
                'getRegion',
                'getRegionId',
                'addData',
                'setData',
                'setCustomerId',
                'setPostIndex',
                'setFirstname',
                'load',
                'save',
                '__sleep',
                '__wakeup',
                'getDefaultAttributeCodes',
                'getAttributes',
                'getData',
                'getCustomerId',
                'getParentId',
                'delete',
                'validate',
                'getCountryModel',
                'getRegionCollection',
                'getSize',
                'setCustomer'
            )
        )->getMock();
        $addressMock->expects($this->any())->method('getId')->will($this->returnValue($addrId));
        $addressMock->expects($this->any())->method('getRegion')->will($this->returnValue(self::REGION));
        $addressMock->expects($this->any())->method('getRegionId')->will($this->returnValue(self::REGION_ID));
        $addressMock->expects($this->any())->method('getCustomerId')->will($this->returnValue($customerId));
        $addressMock->expects($this->never())->method('validate')->will($this->returnValue(true));
        $addressMock->expects($this->any())->method('getCountryModel')->will($this->returnSelf());
        $addressMock->expects($this->any())->method('getRegionCollection')->will($this->returnSelf());
        $addressMock->expects($this->any())->method('getSize')->will($this->returnValue(0));

        $map = array(
            array('firstname', null, $firstName),
            array('lastname', null, self::LASTNAME),
            array('street', null, self::STREET),
            array('city', null, self::CITY),
            array('postcode', null, self::POSTCODE),
            array('telephone', null, self::TELEPHONE),
            array('region', null, self::REGION),
            array('country_id', null, self::COUNTRY_ID)
        );

        $addressMock->expects($this->any())->method('getData')->will($this->returnValueMap($map));

        $addressMock->expects($this->any())->method('load')->will($this->returnSelf());
        $addressMock->expects(
            $this->any()
        )->method(
            'getDefaultAttributeCodes'
        )->will(
            $this->returnValue(array('entity_id', 'attribute_set_id'))
        );
        $addressMock->expects($this->any())->method('getAttributes')->will($this->returnValue($attributes));
        return $addressMock;
    }

    private function _createAttribute($attributeCode)
    {
        $attribute = $this->getMockBuilder(
            '\Magento\Customer\Model\Attribute'
        )->disableOriginalConstructor()->getMock();
        $attribute->expects($this->any())->method('getAttributeCode')->will($this->returnValue($attributeCode));
        return $attribute;
    }
}
