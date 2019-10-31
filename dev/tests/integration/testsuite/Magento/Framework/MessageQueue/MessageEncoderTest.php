<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue;

use LogicException;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerExtension;
use Magento\Customer\Api\Data\CustomerExtensionInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Framework\Communication\Config;
use Magento\Framework\Communication\Config\Data;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MessageEncoderTest extends TestCase
{
    /**
     * @var MessageEncoder
     */
    private $encoder;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
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
    public function testEncode(): void
    {
        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $fixtureCustomerId = 1;
        $customer = $customerRepository->getById($fixtureCustomerId);
        /** @var CustomerExtensionInterface $customerExtension */
        $customerExtension = $this->objectManager->create(CustomerExtension::class);
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
    public function testEncodeArrayOfEntities(): void
    {
        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $fixtureCustomerId = 1;
        $customer = $customerRepository->getById($fixtureCustomerId);
        /** @var CustomerExtensionInterface $customerExtension */
        $customerExtension = $this->objectManager->create(CustomerExtension::class);
        $customerExtension->setTestGroupCode('Some Group Code');
        $customer->setExtensionAttributes($customerExtension);
        $encodedCustomerData = json_decode($this->encoder->encode('customer.list.retrieved', [$customer]), true);
        $createdAt = $customer->getCreatedAt();
        $updatedAt = $customer->getUpdatedAt();
        $expectedEncodedCustomerData = json_decode($this->getCustomerDataAsJson($createdAt, $updatedAt), true);
        $this->assertEquals($expectedEncodedCustomerData, $encodedCustomerData[0]);
    }

    public function testDecode(): void
    {
        $encodedMessage = $this->getCustomerDataAsJson('2015-07-22 12:43:36', '2015-07-22 12:45:36');
        /** @var CustomerInterface $decodedCustomerObject */
        $decodedCustomerObject = $this->encoder->decode('customer.created', $encodedMessage);
        $this->assertInstanceOf(CustomerInterface::class, $decodedCustomerObject);
        $this->assertEquals('customer@example.com', $decodedCustomerObject->getEmail());
        $this->assertEquals(1, $decodedCustomerObject->getGroupId());

        $this->assertInstanceOf(
            CustomerExtensionInterface::class,
            $decodedCustomerObject->getExtensionAttributes()
        );
        $this->assertEquals('Some Group Code', $decodedCustomerObject->getExtensionAttributes()->getTestGroupCode());
        $addresses = $decodedCustomerObject->getAddresses();
        $this->assertCount(1, $addresses, "Address was not decoded.");
        $this->assertInstanceOf(
            AddressInterface::class,
            $addresses[0]
        );
        $this->assertEquals('3468676', $addresses[0]->getTelephone());
        $this->assertEquals(true, $addresses[0]->isDefaultBilling());

        $this->assertInstanceOf(
            RegionInterface::class,
            $addresses[0]->getRegion()
        );
        $this->assertEquals('AL', $addresses[0]->getRegion()->getRegionCode());
    }

    public function testDecodeInvalidMessageFormat(): void
    {
        $this->expectExceptionMessage('Error occurred during message decoding');
        $this->expectException(LocalizedException::class);
        $this->encoder->decode('customer.created', "{");
    }

    public function testDecodeInvalidMessage(): void
    {
        $message =
            'Cannot inject property "not_existing_field" in class "Magento\Customer\Api\Data\CustomerInterface".';
        $this->expectExceptionMessage($message);
        $this->expectException(LogicException::class);
        $this->encoder->decode('customer.created', '{"not_existing_field": "value"}');
    }

    public function testDecodeIncorrectMessage(): void
    {
        $this->expectExceptionMessage('Error occurred during message decoding');
        $this->expectException(LocalizedException::class);
        $this->encoder->decode('customer.created', "{");
    }

    /**
     * @return mixed
     */
    protected function getConfig()
    {
        $newData = include __DIR__ . '/_files/encoder_communication.php';
        /** @var Data $configData */
        $configData = $this->objectManager->create(Data::class);
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
    protected function getCustomerDataAsJson(string $createdAt, string $updatedAt): string
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
     * @throws ReflectionException
     */
    public function setBackwardCompatibleProperty($object, string $propertyName, $propertyValue): void
    {
        $reflection = new ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($propertyName);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $propertyValue);
    }
}
