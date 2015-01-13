<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Helper;

use Magento\Customer\Api\Data\AddressDataBuilder;
use Magento\Customer\Api\Data\CustomerDataBuilder;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Data\Customer as CustomerData;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Webapi\Model\Rest\Config as RestConfig;

class Customer extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/customers';
    const SERVICE_NAME = 'customerAccountManagementV1';
    const SERVICE_VERSION = 'V1';

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

    /** Sample values for testing */
    const FIRSTNAME = 'Jane';
    const LASTNAME = 'Doe';
    const PASSWORD = 'test@123';

    const ADDRESS_CITY1 = 'CityM';
    const ADDRESS_CITY2 = 'CityX';
    const ADDRESS_REGION_CODE1 = 'AL';
    const ADDRESS_REGION_CODE2 = 'AL';

    /** @var AddressDataBuilder */
    private $addressBuilder;

    /** @var CustomerDataBuilder */
    private $customerBuilder;

    /** @var DataObjectProcessor */
    private $dataObjectProcessor;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->addressBuilder = Bootstrap::getObjectManager()->create(
            'Magento\Customer\Api\Data\AddressDataBuilder'
        );

        $this->customerBuilder = Bootstrap::getObjectManager()->create(
            'Magento\Customer\Api\Data\CustomerDataBuilder'
        );

        $this->dataObjectProcessor = Bootstrap::getObjectManager()->create(
            'Magento\Framework\Reflection\DataObjectProcessor'
        );
    }

    public function createSampleCustomer()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => RestConfig::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'CreateAccount',
            ],
        ];
        $customerDataArray = $this->dataObjectProcessor->buildOutputDataArray(
            $this->createSampleCustomerDataObject(),
            '\Magento\Customer\Api\Data\CustomerInterface'
        );
        $requestData = ['customer' => $customerDataArray, 'password' => self::PASSWORD];
        $customerData = $this->_webApiCall($serviceInfo, $requestData);
        return $customerData;
    }

    /**
     * Create customer using setters.
     *
     * @return CustomerInterface
     */
    public function createSampleCustomerDataObject()
    {
        $this->addressBuilder
            ->setCountryId('US')
            ->setDefaultBilling(true)
            ->setDefaultShipping(true)
            ->setPostcode('75477')
            ->setRegion(
                Bootstrap::getObjectManager()->create('Magento\Customer\Api\Data\RegionDataBuilder')
                    ->setRegionCode(self::ADDRESS_REGION_CODE1)
                    ->setRegion('Alabama')
                    ->setRegionId(1)
                    ->create()
            )
            ->setStreet(['Green str, 67'])
            ->setTelephone('3468676')
            ->setCity(self::ADDRESS_CITY1)
            ->setFirstname('John')
            ->setLastname('Smith');
        $address1 = $this->dataObjectProcessor->buildOutputDataArray(
            $this->addressBuilder->create(),
            'Magento\Customer\Api\Data\AddressInterface'
        );

        $this->addressBuilder
            ->setCountryId('US')
            ->setDefaultBilling(false)
            ->setDefaultShipping(false)
            ->setPostcode('47676')
            ->setRegion(
                Bootstrap::getObjectManager()->create('Magento\Customer\Api\Data\RegionDataBuilder')
                    ->setRegionCode(self::ADDRESS_REGION_CODE2)
                    ->setRegion('Alabama')
                    ->setRegionId(1)
                    ->create()
            )
            ->setStreet(['Black str, 48', 'Building D'])
            ->setCity(self::ADDRESS_CITY2)
            ->setTelephone('3234676')
            ->setFirstname('John')
            ->setLastname('Smith');
        $address2 = $this->dataObjectProcessor->buildOutputDataArray(
            $this->addressBuilder->create(),
            'Magento\Customer\Api\Data\AddressInterface'
        );

        $customerData = [
            CustomerData::FIRSTNAME => self::FIRSTNAME,
            CustomerData::LASTNAME => self::LASTNAME,
            CustomerData::EMAIL => 'janedoe' . uniqid() . '@example.com',
            CustomerData::CONFIRMATION => self::CONFIRMATION,
            CustomerData::CREATED_AT => self::CREATED_AT,
            CustomerData::CREATED_IN => self::STORE_NAME,
            CustomerData::DOB => self::DOB,
            CustomerData::GENDER => self::GENDER,
            CustomerData::GROUP_ID => self::GROUP_ID,
            CustomerData::MIDDLENAME => self::MIDDLENAME,
            CustomerData::PREFIX => self::PREFIX,
            CustomerData::STORE_ID => self::STORE_ID,
            CustomerData::SUFFIX => self::SUFFIX,
            CustomerData::TAXVAT => self::TAXVAT,
            CustomerData::WEBSITE_ID => self::WEBSITE_ID,
            CustomerData::KEY_ADDRESSES => [$address1, $address2],
            'custom_attributes' => [
                [
                    'attribute_code' => 'disable_auto_group_change',
                    'value' => '0',
                ],
            ],
        ];
        return $this->customerBuilder->populateWithArray($customerData)->create();
    }
}
