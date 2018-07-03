<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\Communication\Config;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MessageEncoderTest extends \PHPUnit\Framework\TestCase
{
    /** @var MessageEncoder */
    protected $encoder;

    /** @var \Magento\Framework\ObjectManagerInterface */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->encoder = $this->objectManager->create(MessageEncoder::class);
        $this->setBackwardCompatibleProperty(
            $this->encoder,
            'communicationConfig',
            $this->getConfig()
        );
        parent::setUp();
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testEncode()
    {
        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
        $customerRepository = $this->objectManager->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $fixtureCustomerId = 1;
        $customer = $customerRepository->getById($fixtureCustomerId);
        /** @var \Magento\Customer\Api\Data\CustomerExtensionInterface $customerExtension */
        $customerExtension = $this->objectManager->create(\Magento\Customer\Api\Data\CustomerExtension::class);
        $customerExtension->setTestGroupCode('Some Group Code');
        $customer->setExtensionAttributes($customerExtension);
        $encodedCustomerData = json_decode($this->encoder->encode('customer.created', $customer), true);
        $createdAt = $customer->getCreatedAt();
        $updatedAt = $customer->getUpdatedAt();
        $expectedEncodedCustomerData = json_decode($this->getCustomerDataAsJson($createdAt, $updatedAt), true);
        $this->assertEquals($expectedEncodedCustomerData, $encodedCustomerData);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testEncodeArrayOfEntities()
    {
        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
        $customerRepository = $this->objectManager->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $fixtureCustomerId = 1;
        $customer = $customerRepository->getById($fixtureCustomerId);
        /** @var \Magento\Customer\Api\Data\CustomerExtensionInterface $customerExtension */
        $customerExtension = $this->objectManager->create(\Magento\Customer\Api\Data\CustomerExtension::class);
        $customerExtension->setTestGroupCode('Some Group Code');
        $customer->setExtensionAttributes($customerExtension);
        $encodedCustomerData = json_decode($this->encoder->encode('customer.list.retrieved', [$customer]), true);
        $createdAt = $customer->getCreatedAt();
        $updatedAt = $customer->getUpdatedAt();
        $expectedEncodedCustomerData = json_decode($this->getCustomerDataAsJson($createdAt, $updatedAt), true);
        $this->assertEquals($expectedEncodedCustomerData, $encodedCustomerData[0]);
    }

    public function testDecode()
    {
        $encodedMessage = $this->getCustomerDataAsJson('2015-07-22 12:43:36', '2015-07-22 12:45:36');
        /** @var \Magento\Customer\Api\Data\CustomerInterface $decodedCustomerObject */
        $decodedCustomerObject = $this->encoder->decode('customer.created', $encodedMessage);
        $this->assertInstanceOf(\Magento\Customer\Api\Data\CustomerInterface::class, $decodedCustomerObject);
        $this->assertEquals('customer@example.com', $decodedCustomerObject->getEmail());
        $this->assertEquals(1, $decodedCustomerObject->getGroupId());

        $this->assertInstanceOf(
            \Magento\Customer\Api\Data\CustomerExtensionInterface::class,
            $decodedCustomerObject->getExtensionAttributes()
        );
        $this->assertEquals('Some Group Code', $decodedCustomerObject->getExtensionAttributes()->getTestGroupCode());
        $addresses = $decodedCustomerObject->getAddresses();
        $this->assertCount(1, $addresses, "Address was not decoded.");
        $this->assertInstanceOf(
            \Magento\Customer\Api\Data\AddressInterface::class,
            $addresses[0]
        );
        $this->assertEquals('3468676', $addresses[0]->getTelephone());
        $this->assertEquals(true, $addresses[0]->isDefaultBilling());

        $this->assertInstanceOf(
            \Magento\Customer\Api\Data\RegionInterface::class,
            $addresses[0]->getRegion()
        );
        $this->assertEquals('AL', $addresses[0]->getRegion()->getRegionCode());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Error occurred during message decoding
     */
    public function testDecodeInvalidMessageFormat()
    {
        $this->encoder->decode('customer.created', "{");
    }

    /**
     * @expectedException \LogicException
     */
    public function testDecodeInvalidMessage()
    {
        $message = 'Property "NotExistingField" does not have accessor method "getNotExistingField" in class '
            . '"Magento\Customer\Api\Data\CustomerInterface".';
        $this->expectExceptionMessage($message);
        $this->encoder->decode('customer.created', '{"not_existing_field": "value"}');
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Error occurred during message decoding
     */
    public function testDecodeIncorrectMessage()
    {
        $this->encoder->decode('customer.created', "{");
    }

    /**
     * @return \Magento\Framework\MessageQueue\Config
     */
    protected function getConfig()
    {
        $newData = include __DIR__ . '/_files/encoder_communication.php';
        /** @var \Magento\Framework\Communication\Config\Data $configData */
        $configData = $this->objectManager->create(\Magento\Framework\Communication\Config\Data::class);
        $configData->reset();
        $configData->merge($newData);
        $config = $this->objectManager->create(Config::class, ['configData' => $configData]);

        return $config;
    }

    /**
     * Get fixture customer data in Json format
     *
     * @param string $createdAt
     * @param string $updatedAt
     * @return string
     */
    protected function getCustomerDataAsJson($createdAt, $updatedAt)
    {
        return <<<JSON
{
    "id": 1,
    "group_id": 1,
    "default_billing": "1",
    "default_shipping": "1",
    "created_at": "{$createdAt}",
    "updated_at": "{$updatedAt}",
    "email": "customer@example.com",
    "firstname": "John",
    "lastname": "Smith",
    "middlename": "A",
    "prefix": "Mr.",
    "suffix": "Esq.",
    "gender": 0,
    "store_id": 1,
    "taxvat": "12",
    "website_id": 1,
    "addresses": [
        {
            "id": 1,
            "customer_id": 1,
            "region": {
                "region_code": "AL",
                "region": "Alabama",
                "region_id": 1
            },
            "region_id": 1,
            "country_id": "US",
            "street": [
                "Green str, 67"
            ],
            "company": "CompanyName",
            "telephone": "3468676",
            "postcode": "75477",
            "city": "CityM",
            "firstname": "John",
            "lastname": "Smith",
            "default_shipping": true,
            "default_billing": true
        }
    ],
    "disable_auto_group_change": 0,
    "extension_attributes": {
        "test_group_code": "Some Group Code"
    }
}
JSON;
    }

    /**
     * Set mocked property
     *
     * @param object $object
     * @param string $propertyName
     * @param object $propertyValue
     * @return void
     */
    public function setBackwardCompatibleProperty($object, $propertyName, $propertyValue)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($propertyName);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $propertyValue);
    }
}
