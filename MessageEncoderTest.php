<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue;

use Magento\Framework\ObjectManagerInterface;

class MessageEncoderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\MessageQueue\MessageEncoder */
    protected $encoder;

    /** @var \Magento\Framework\ObjectManagerInterface */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->encoder = $this->objectManager->create(
            'Magento\Framework\MessageQueue\MessageEncoder',
            ['queueConfig' => $this->getConfig()]
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
        $customerRepository = $this->objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
        $fixtureCustomerId = 1;
        $customer = $customerRepository->getById($fixtureCustomerId);
        /** @var \Magento\Customer\Api\Data\CustomerExtensionInterface $customerExtension */
        $customerExtension = $this->objectManager->create('Magento\Customer\Api\Data\CustomerExtension');
        $customerExtension->setTestGroupCode('Some Group Code');
        $customer->setExtensionAttributes($customerExtension);
        $encodedCustomerData = json_decode($this->encoder->encode('customer.created', $customer), true);
        $createdAt = $customer->getCreatedAt();
        $expectedEncodedCustomerData = json_decode($this->getCustomerDataAsJson($createdAt), true);
        $this->assertEquals($expectedEncodedCustomerData['data'], $encodedCustomerData['data']);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testEncodeArrayOfEntities()
    {
        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
        $customerRepository = $this->objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
        $fixtureCustomerId = 1;
        $customer = $customerRepository->getById($fixtureCustomerId);
        /** @var \Magento\Customer\Api\Data\CustomerExtensionInterface $customerExtension */
        $customerExtension = $this->objectManager->create('Magento\Customer\Api\Data\CustomerExtension');
        $customerExtension->setTestGroupCode('Some Group Code');
        $customer->setExtensionAttributes($customerExtension);
        $encodedCustomerData = json_decode($this->encoder->encode('customer.list.retrieved', [$customer]), true);
        $createdAt = $customer->getCreatedAt();
        $expectedEncodedCustomerData = json_decode($this->getCustomerDataAsJson($createdAt), true);
        $this->assertEquals($expectedEncodedCustomerData['data'], $encodedCustomerData['data'][0]);
    }

    public function testDecode()
    {
        $encodedMessage = $this->getCustomerDataAsJson('2015-07-22 12:43:36');
        /** @var \Magento\Customer\Api\Data\CustomerInterface $decodedCustomerObject */
        $decodedCustomerObject = $this->encoder->decode('customer.created', $encodedMessage);
        $this->assertInstanceOf('Magento\Customer\Api\Data\CustomerInterface', $decodedCustomerObject);
        $this->assertEquals('customer@example.com', $decodedCustomerObject->getEmail());
        $this->assertEquals(1, $decodedCustomerObject->getGroupId());

        $this->assertInstanceOf(
            'Magento\Customer\Api\Data\CustomerExtensionInterface',
            $decodedCustomerObject->getExtensionAttributes()
        );
        $this->assertEquals('Some Group Code', $decodedCustomerObject->getExtensionAttributes()->getTestGroupCode());
        $addresses = $decodedCustomerObject->getAddresses();
        $this->assertCount(1, $addresses, "Address was not decoded.");
        $this->assertInstanceOf(
            'Magento\Customer\Api\Data\AddressInterface',
            $addresses[0]
        );
        $this->assertEquals('3468676', $addresses[0]->getTelephone());
        $this->assertEquals(true, $addresses[0]->isDefaultBilling());

        $this->assertInstanceOf(
            'Magento\Customer\Api\Data\RegionInterface',
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
     * @expectedExceptionMessage Property "NotExistingField" does not have corresponding setter
     */
    public function testDecodeInvalidMessage()
    {
        $this->encoder->decode('customer.created', '{"data": {"not_existing_field": "value"}}');
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
     * @return \Magento\Framework\MessageQueue\ConfigInterface
     */
    protected function getConfig()
    {
        $configPath = __DIR__ . '/etc/queue.xml';
        $fileResolverMock = $this->getMock('Magento\Framework\Config\FileResolverInterface');
        $fileResolverMock->expects($this->any())
            ->method('get')
            ->willReturn([$configPath => file_get_contents(($configPath))]);

        /** @var \Magento\Framework\MessageQueue\Config\Reader\Xml $xmlReader */
        $xmlReader = $this->objectManager->create(
            '\Magento\Framework\MessageQueue\Config\Reader\Xml',
            ['fileResolver' => $fileResolverMock]
        );

        $newData = $xmlReader->read();

        /** @var \Magento\Framework\MessageQueue\Config\Data $configData */
        $configData = $this->objectManager->create('Magento\Framework\MessageQueue\Config\Data');
        $configData->reset();
        $configData->merge($newData);
        $config = $this->objectManager->create(
            'Magento\Framework\MessageQueue\ConfigInterface',
            ['queueConfigData' => $configData]
        );

        return $config;
    }

    /**
     * Get fixture customer data in Json format
     *
     * @param string $createdAt
     * @return string
     */
    protected function getCustomerDataAsJson($createdAt)
    {
        return <<<JSON
{
    "data":
    {
        "id": 1,
        "group_id": 1,
        "default_billing": "1",
        "default_shipping": "1",
        "created_at": "{$createdAt}",
        "updated_at": "{$createdAt}",
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
}
JSON;
    }
}
