<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Address;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Address as AddressModel;
use Magento\Customer\Model\Config\Share;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Address\Attribute\Source\CountryWithWebsites;
use Magento\Customer\Model\ResourceModel\Address\Collection as AddressCollection;
use Magento\Directory\Model\AllowedCountries;
use Magento\Customer\Model\ResourceModel\Address\CollectionFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Address\CustomerAddressDataFormatter;
use Magento\Customer\Model\Address\CustomerAddressDataProvider;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerAddressDataProviderTest extends TestCase
{
    const ORIG_CUSTOMER_ID = 1;
    const ORIG_PARENT_ID = 2;

    /**
     * @var CollectionFactory|MockObject
     */
    private $addressCollectionFactory;

    /**
     * @var AddressCollection|MockObject
     */
    private $collection;

    /**
     * @var AllowedCountries|MockObject
     */
    private $allowedCountriesMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var Share|MockObject
     */
    private $shareConfigMock;

    /**
     * @var CustomerAddressDataFormatter
     */
    private $customerAddressDataFormatter;

    /**
     * @var CustomerAddressDataProvider
     */
    private $customerAddressDataProvider;

    /**
     * @var AddressModel
     */
    protected $address;

    /**
     * @var CustomerInterface|MockObject
     */
    private $customerMock;

    /**
     * @var AddressInterface|MockObject
     */
    private $addressMock;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var AddressInterfaceFactory */
    private $addressFactory;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->customerMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $this->addressMock = $this->getMockForAbstractClass(AddressInterface::class);
        ##############
        $this->addressCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->collection = $this->getMockBuilder(AddressCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepository = $this->getMockForAbstractClass(CustomerRepositoryInterface::class);
        /*$this->addressCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->collection);*/
        //$this->customer = $this->getMockForAbstractClass(CustomerInterface::class);
        $this->address = $this->getMockBuilder(AddressModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerFactory = $this->getMockBuilder(CustomerInterfaceFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressFactory = $this->getMockBuilder(AddressInterfaceFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        /*$this->address = $this->getMockBuilder(AddressModel::class)
            ->disableOriginalConstructor()
            ->getMock();*/
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);

        ############
        $this->allowedCountriesMock = $this->getMockBuilder(AllowedCountries::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->shareConfigMock = $this->getMockBuilder(Share::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerAddressDataFormatter = $this->getMockBuilder(CustomerAddressDataFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerAddressDataProvider = new CustomerAddressDataProvider(
            $this->customerAddressDataFormatter,
            $this->shareConfigMock,
            $this->allowedCountriesMock
        );

    }

    public function testV1()
    {

        $this->customerMock->expects($this->once())
            ->method('getAddresses')
            ->willReturnSelf();
        $this->customerAddressDataFormatter->expects($this->once())
            ->method('prepareAddress')
            ->willReturnSelf();

        $result = $this->customerAddressDataProvider->getAddressDataByCustomer($this->customerMock);
        $this->assertEmpty($result);
    }




    public function testABC()
    {
        $addressData = [
            'id' => 1,
            'parent_id' => 1,
            'firstname' => 'F',
            'lastname' => 'Doe',
            'street' => "Street 1\nStreet 2",
            'city' => 'Austin',
            'postcode' => 07201,
            'region_id' => 1,
            'company' => 'Magento',
            'fax' => '222-22-22',
        ];
        $customerData = [
            'firstname' => 'Jhon',
            'lastname' => 'Doe',
            'email' => 'customer@email.com',
        ];

        $customerFactory = $this->createPartialMock(CustomerFactory::class, ['create']);
        $customerFactory->expects($this->any())->method('create')->will($this->returnValue($this->customerMock));
        $this->customerMock->setData($customerData);
        $addressMock = $this->getMockBuilder(Address::class)
            ->onlyMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $addressMock->setData($addressData);
        $this->customerMock->addAddress($addressMock);
    }




    public function testGetAddressDataByCustomer()
    {
        $addressMock = $this->getMockBuilder(AddressInterface::class)
            ->setMethods(
                [
                    'getId',
                    'getCountryId',
                    'setData',
                    'getData'
                ]
            )
            ->getMockForAbstractClass();

        $expectedData = [
            '1' => [
                'parent_id' => '1',
                'default_billing' => '1',
                'default_shipping' => '1',
                'firstname' => 'John',
                'lastname' => 'Doe',
                'street' => [
                    '42000 Ave W 55 Cedar City',
                    'Apt. 33'
                ]
            ]
        ];

        $this->addressMock->expects($this->once())
            ->method('getId')
            ->willReturn('1');

        $this->customerAddressDataFormatter->expects($this->once())
            ->method('prepareAddress')
            ->willReturnSelf();

        $this->customerMock->expects($this->any())
            ->method('getAddresses')
            ->willReturn($expectedData);

        $this->addressMock->expects($this->any())->method('getCountryId')->willReturn('US');

        $result = $this->customerAddressDataProvider->getAddressDataByCustomer($this->customerMock);
        $this->assertEmpty($result);
    }




    public function testCreateNewCustomerWithAddress(): void
    {
        $availableCountry = 'BD';
        $address = $this->addressFactory->create();
        $address->setCountryId($availableCountry)
            ->setPostcode('75477')
            ->setRegionId(1)
            ->setStreet(['Green str, 67'])
            ->setTelephone('3468676')
            ->setCity('CityM')
            ->setFirstname('John')
            ->setLastname('Smith')
            ->setIsDefaultShipping(true)
            ->setIsDefaultBilling(true);
        $customerEntity = $this->customerFactory->create();
        $customerEntity->setEmail('test@example.com')
            ->setFirstname('John')
            ->setLastname('Smith')
            ->setStoreId(1);
        $customerEntity->setAddresses([$address]);
        $this->customer = $this->accountManagement->createAccount($customerEntity);
        $this->assertCount(1, $this->customer->getAddresses(), 'The available address wasn\'t saved.');
        $this->assertSame(
            $availableCountry,
            $this->customer->getAddresses()[0]->getCountryId(),
            'The address was saved with disallowed country.'
        );
    }




    public function testGetAddressDataByCustomer2()
    {
        $allowedCountries = ['IN', 'FR'];
        $expectedResult = [
            'id' => 1,
            'parent_id' => 1,
            'firstname' => 'John',
            'lastname' => 'Doe',
            'street' => "Street 1\nStreet 2",
            'city' => 'Austin',
            'postcode' => 07201,
            'region_id' => 1,
            'company' => 'Magento',
            'fax' => '222-22-22',
        ];
        $expectedResultWithAllowedCountry = array_merge(
            $expectedResult,
            [
                'country_id' => 'IN',
            ]
        );
        $expectedResultWithoutAllowedCountry = array_merge(
            $expectedResult,
            [
                'country_id' => 'US',
            ]
        );

        $addressMock = $this->getMockBuilder(Address::class)
            ->onlyMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $addressMock->setData(
            [
                'entity_id' => 1,
                'attribute_set_id' => 2,
                'telephone' => 3468676,
                'postcode' => 75477,
                'country_id' => 'US',
                'city' => 'CityM',
                'company' => 'CompanyName',
                'street' => 'Green str, 67',
                'lastname' => 'Smith',
                'firstname' => 'John',
                'parent_id' => 1,
                'region_id' => 1,
            ]
        );

        $customerMock1 = $this->getMockForAbstractClass(CustomerInterface::class);
        $customerMock2 = $this->getMockForAbstractClass(CustomerInterface::class);
        $customerMock2->setId(1);
        $customerMock2->setAddresses($addressMock);

        $customerMock1->expects($this->atLeastOnce())
            ->method('getAddresses')
            ->willReturn($expectedResultWithAllowedCountry);
        $customerMock2->expects($this->atLeastOnce())
            ->method('getAddresses')
            ->willReturn($expectedResultWithAllowedCountry);

        $result = $this->customerAddressDataProvider->getAddressDataByCustomer($customerMock2);
        $this->assertEmpty($result);
        //$this->assertEquals($expectedResultWithAllowedCountry, $result);

        /*$customersIds = [10, 11, 12];
        $customerMock = $this->getMockBuilder(CustomerInterface::class)
            ->setMethods(['setData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();*/

        /*$customerData->expects($this->once())
            ->method('setId')
            ->with(1)
            ->willReturnSelf();

        $this->customerFactory->expects($this->once())
            ->method('create')
            ->willReturn($customerData);*/

        /*$customerDataArray = ['entity_id' => 1];
        $customerModel->expects($this->once())
            ->method('getData')
            ->willReturn($customerDataArray);*/





        /*$customerDataObject = $this->getMockForAbstractClass(CustomerInterface::class);
        $customerDataFactory = $this->getMockBuilder(CustomerInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $customerDataFactory->expects($this->atLeastOnce())->method('create')->willReturn($customerDataObject);*/

        //$customerMock = $this->getMockForAbstractClass(CustomerInterfa
        $regionId = 1;
        /*$this->address->setData('id', 1);
        $this->address->setData('parent_id', 1);
        $this->address->setData('firstname', 'John');
        $this->address->setData('lastname', 'Doe');
        $this->address->setData('street', "Street 1\nStreet 2");
        $this->address->setData('city', 'Austin');
        $this->address->setData('postcode', 07201);
        $this->address->setData('region_id', 1);
        $this->address->setData('company', 'Magento');
        $this->address->setData('fax', '222-22-22');
        $this->address->setData('country_id', 1);*/
        /*$this->address->setData($expectedResultWithAllowedCountry);
        var_dump($this->address->getData());
        $this->customer->expects($this->any())->method('getAddresses')->willReturn($this->address);



        $result = $this->customerAddressDataProvider->getAddressDataByCustomer($this->customer);
        $this->assertEmpty($result);*/
        //$this->assertEquals($expectedResultWithAllowedCountry, $result);

        /*$customer1 = $this->getMockForAbstractClass(CustomerInterface::class);
        $customer2 = $this->getMockForAbstractClass(CustomerInterface::class);

        $customer1->expects($this->atLeastOnce())
            ->method('getAddresses')
            ->willReturnSelf();*/

        /*$address = $customer1->expects($this->atLeastOnce())
            ->method('getAddresses');*/

       /* $customer1->expects($this->atLeastOnce())
            ->method('getWebsiteId')
            ->willReturn(1);
        $customer2->expects($this->atLeastOnce())
            ->method('getWebsiteId')
            ->willReturn(2);*/


        /*$this->allowedCountriesMock->expects($this->exactly(2))
            ->method('getAllowedCountries')
            ->withConsecutive(
                ['website', 1],
                ['website', 2]
            )
            ->willReturnMap([
                ['website', 1, ['AM' => 'AM']],
                ['website', 2, ['AM' => 'AM', 'DZ' => 'DZ']]
            ]);*/

        /*$result = $this->customerAddressDataProvider->getAddressDataByCustomer($customer1);
        var_dump($result);*/

    }
}
