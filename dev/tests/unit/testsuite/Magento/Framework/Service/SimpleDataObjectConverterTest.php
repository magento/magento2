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

namespace Magento\Framework\Service;

use Magento\Customer\Service\V1\Data\Customer;

/**
 * Class implements tests for SimpleDataObjectConverter class.
 */
class SimpleDataObjectConverterTest extends \PHPUnit_Framework_TestCase
{
    /** @var SimpleDataObjectConverter */
    protected $dataObjectConverter;

    const CONFIRMATION = 'a4fg7h893e39d';
    const CREATED_AT = '2013-11-05';
    const CREATED_IN = 'default';
    const STORE_NAME = 'Store Name';
    const DOB = '1970-01-01';
    const GENDER = 'Male';
    const GROUP_ID = 1;
    const MIDDLENAME = 'A';
    const PREFIX = 'Mr.';
    const STORE_ID = 1;
    const SUFFIX = 'Esq.';
    const TAXVAT = '12';
    const WEBSITE_ID = 1;
    const ID = 1;
    const FIRSTNAME = 'Jane';
    const LASTNAME = 'Doe';
    const ATTRIBUTE_CODE = 'attribute_code';
    const ATTRIBUTE_VALUE = 'attribute_value';
    const REGION_CODE = 'AL';
    const REGION_ID = '1';
    const REGION = 'Alabama';

    /**
     * Expected street in customer addresses
     *
     * @var array
     */
    private $expectedStreet = [['Green str, 67'], ['Black str, 48', 'Building D']];

    /**
     * Set up helper.
     */
    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->dataObjectConverter = $objectManager->getObject('Magento\Framework\Service\SimpleDataObjectConverter');
        parent::setUp();
    }

    public function testToFlatArray()
    {
        //Unpack Data Object as an array and convert keys to camelCase to match property names in WSDL
        $response = SimpleDataObjectConverter::toFlatArray($this->getCustomerDetails());
        //Check if keys are correctly converted to camel case wherever necessary
        $this->assertEquals(self::FIRSTNAME, $response['firstname']);
        $this->assertEquals(self::GROUP_ID, $response['group_id']);
        $this->assertEquals(self::REGION, $response['region']);
        $this->assertEquals(self::REGION_CODE, $response['region_code']);
        $this->assertEquals(self::REGION_ID, $response['region_id']);
        //TODO : FIX toFlatArray since it has issues in converting Street array correctly as it overwrites the data.
    }

    public function testConvertKeysToCamelCase()
    {
        //Unpack as an array and convert keys to camelCase to match property names in WSDL
        $response = $this->dataObjectConverter->convertKeysToCamelCase($this->getCustomerDetails()->__toArray());
        //Check if keys are correctly converted to camel case wherever necessary
        $this->assertEquals(self::FIRSTNAME, $response['customer']['firstname']);
        $this->assertEquals(self::GROUP_ID, $response['customer']['groupId']);
        foreach ($response['addresses'] as $key => $address) {
            $region = $address['region'];
            $this->assertEquals(self::REGION, $region['region']);
            $this->assertEquals(self::REGION_CODE, $region['regionCode']);
            $this->assertEquals(self::REGION_ID, $region['regionId']);
            $this->assertEquals($this->expectedStreet[$key], $address['street']);
        }
    }

    public function testConvertSoapStdObjectToArray()
    {
        $stdObject = json_decode(json_encode($this->getCustomerDetails()->__toArray()), false);
        $addresses = $stdObject->addresses;
        unset($stdObject->addresses);
        $stdObject->addresses = new \stdClass();
        $stdObject->addresses->item = $addresses;
        $response = $this->dataObjectConverter->convertStdObjectToArray($stdObject);

        //Check array conversion
        $this->assertTrue(is_array($response['customer']));
        $this->assertTrue(is_array($response['addresses']));
        $this->assertEquals(2, count($response['addresses']['item']));

        //Check if data is correct
        $this->assertEquals(self::FIRSTNAME, $response['customer']['firstname']);
        $this->assertEquals(self::GROUP_ID, $response['customer']['group_id']);
        foreach ($response['addresses']['item'] as $key => $address) {
            $region = $address['region'];
            $this->assertEquals(self::REGION, $region['region']);
            $this->assertEquals(self::REGION_CODE, $region['region_code']);
            $this->assertEquals(self::REGION_ID, $region['region_id']);
            $this->assertEquals($this->expectedStreet[$key], $address['street']);
        }
    }

    /**
     * Get a sample Customer details data object
     *
     * @return \Magento\Customer\Service\V1\Data\CustomerDetails
     */
    private function getCustomerDetails()
    {
        $objectManager =  new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var \Magento\Customer\Service\V1\Data\AddressBuilder $addressBuilder */
        $addressBuilder = $objectManager->getObject('Magento\Customer\Service\V1\Data\AddressBuilder');
        /** @var \Magento\Customer\Service\V1\CustomerMetadataServiceInterface $metadataService */
        $metadataService = $this->getMockBuilder('Magento\Customer\Service\V1\CustomerMetadataService')
            ->disableOriginalConstructor()
            ->getMock();
        $metadataService->expects($this->any())
            ->method('getCustomAttributesMetadata')
            ->will($this->returnValue([]));
        /** @var \Magento\Customer\Service\V1\Data\CustomerBuilder $customerBuilder */
        $customerBuilder = $objectManager->getObject(
            'Magento\Customer\Service\V1\Data\CustomerBuilder',
            ['metadataService' => $metadataService]
        );
        /** @var \Magento\Customer\Service\V1\Data\CustomerDetailsBuilder $customerDetailsBuilder */
        $customerDetailsBuilder =
            $objectManager->getObject('Magento\Customer\Service\V1\Data\CustomerDetailsBuilder');

        $street1 = ['Green str, 67'];
        $street2 = ['Black str, 48', 'Building D'];
        $addressBuilder->setId(1)
            ->setCountryId('US')
            ->setCustomerId(1)
            ->setDefaultBilling(true)
            ->setDefaultShipping(true)
            ->setPostcode('75477')
            ->setRegion(
                $objectManager->getObject('\Magento\Customer\Service\V1\Data\RegionBuilder')
                    ->setRegionCode(self::REGION_CODE)
                    ->setRegion(self::REGION)
                    ->setRegionId(self::REGION_ID)
                    ->create()
            )
            ->setStreet($street1)
            ->setTelephone('3468676')
            ->setCity('CityM')
            ->setFirstname('John')
            ->setLastname('Smith');
        $address = $addressBuilder->create();

        $addressBuilder->setId(2)
            ->setCountryId('US')
            ->setCustomerId(1)
            ->setDefaultBilling(false)
            ->setDefaultShipping(false)
            ->setPostcode('47676')
            ->setRegion(
                $objectManager->getObject('\Magento\Customer\Service\V1\Data\RegionBuilder')
                    ->setRegionCode(self::REGION_CODE)
                    ->setRegion(self::REGION)
                    ->setRegionId(self::REGION_ID)
                    ->create()
            )
            ->setStreet($street2)
            ->setCity('CityX')
            ->setTelephone('3234676')
            ->setFirstname('John')
            ->setLastname('Smith');
        $address2 = $addressBuilder->create();

        $customerData = [
            Customer::FIRSTNAME => self::FIRSTNAME,
            Customer::LASTNAME => self::LASTNAME,
            Customer::EMAIL => 'janedoe@example.com',
            Customer::CONFIRMATION => self::CONFIRMATION,
            Customer::CREATED_AT => self::CREATED_AT,
            Customer::CREATED_IN => self::STORE_NAME,
            Customer::DOB => self::DOB,
            Customer::GENDER => self::GENDER,
            Customer::GROUP_ID => self::GROUP_ID,
            Customer::MIDDLENAME => self::MIDDLENAME,
            Customer::PREFIX => self::PREFIX,
            Customer::STORE_ID => self::STORE_ID,
            Customer::SUFFIX => self::SUFFIX,
            Customer::TAXVAT => self::TAXVAT,
            Customer::WEBSITE_ID => self::WEBSITE_ID
        ];
        $customerData = $customerBuilder->populateWithArray($customerData)->create();
        $customerDetails = $customerDetailsBuilder->setAddresses([$address, $address2])
            ->setCustomer($customerData)
            ->create();

        return $customerDetails;
    }
}
