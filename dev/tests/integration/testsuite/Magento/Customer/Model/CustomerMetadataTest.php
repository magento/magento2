<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model;

use Magento\TestFramework\Helper\CacheCleaner;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomerMetadataTest extends \PHPUnit_Framework_TestCase
{
    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var CustomerMetadataInterface */
    private $service;

    /** @var CustomerMetadataInterface */
    private $service2;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->configure(
            [\Magento\Framework\Api\ExtensionAttribute\Config\Reader::class => [
                    'arguments' => [
                        'fileResolver' => ['instance' => \Magento\Customer\Model\FileResolverStub::class],
                    ],
                ],
            ]
        );
        $this->customerRepository = $objectManager->create(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        );
        $this->service = $objectManager->create(\Magento\Customer\Api\CustomerMetadataInterface::class);
        $this->service2 = $objectManager->create(\Magento\Customer\Api\CustomerMetadataInterface::class);
        $this->extensibleDataObjectConverter = $objectManager->get(
            \Magento\Framework\Api\ExtensibleDataObjectConverter::class
        );
    }

    public function testGetCustomAttributesMetadata()
    {
        $customAttributesMetadata = $this->service->getCustomAttributesMetadata();
        $this->assertCount(0, $customAttributesMetadata, "Invalid number of attributes returned.");

        $customAttributesMetadata1 = $this->service->getCustomAttributesMetadata();
        $this->assertCount(0, $customAttributesMetadata1, "Invalid number of attributes returned.");

        $customAttributesMetadata2 = $this->service2->getCustomAttributesMetadata();
        $this->assertCount(0, $customAttributesMetadata2, "Invalid number of attributes returned.");
    }

    public function testGetNestedOptionsCustomAttributesMetadata()
    {
        $nestedOptionsAttribute = 'store_id';
        $customAttributesMetadata = $this->service->getAttributeMetadata($nestedOptionsAttribute);
        $options = $customAttributesMetadata->getOptions();
        $nestedOptionExists = false;
        foreach ($options as $option) {
            if (strpos($option->getLabel(), 'Main Website Store') !== false) {
                $this->assertNotEmpty($option->getOptions());
                //Check nested option
                $this->assertTrue(strpos($option->getOptions()[0]->getLabel(), 'Default Store View') !== false);
                $nestedOptionExists = true;
            }
        }
        if (!$nestedOptionExists) {
            $this->fail('Nested attribute options were expected.');
        }

        $customAttributesMetadata1 = $this->service->getAttributeMetadata($nestedOptionsAttribute);
        $customAttributesMetadata1->getOptions();
        $this->assertEquals($customAttributesMetadata, $customAttributesMetadata1);

        $customAttributesMetadata2 = $this->service2->getAttributeMetadata($nestedOptionsAttribute);
        $customAttributesMetadata2->getOptions();
        $this->assertEquals($customAttributesMetadata, $customAttributesMetadata2);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/attribute_user_defined_custom_attribute.php
     */
    public function testGetCustomAttributesMetadataWithAttributeNamedCustomAttribute()
    {
        $customAttributesMetadata = $this->service->getCustomAttributesMetadata();
        $expectedCustomAttributeCodeArray = ['custom_attribute1', 'custom_attribute2', 'customer_image'];
        $actual = [];
        foreach ($customAttributesMetadata as $attribute) {
            $actual[] = $attribute->getAttributeCode();
        }
        $this->assertEquals(
            $expectedCustomAttributeCodeArray,
            array_intersect($expectedCustomAttributeCodeArray, $actual),
            "Expected attributes not returned from the service."
        );

        $customAttributesMetadata1 = $this->service->getCustomAttributesMetadata();
        foreach ($customAttributesMetadata1 as $attribute) {
            $attribute->getAttributeCode();
        }
        $this->assertEquals($customAttributesMetadata, $customAttributesMetadata1);

        $customAttributesMetadata2 = $this->service2->getCustomAttributesMetadata();
        foreach ($customAttributesMetadata2 as $attribute) {
            $attribute->getAttributeCode();
        }
        $this->assertEquals($customAttributesMetadata, $customAttributesMetadata2);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/attribute_user_defined_custom_attribute.php
     */
    public function testGetAllAttributesMetadataWithAttributeNamedCustomAttribute()
    {
        $allAttributesMetadata = $this->service->getAllAttributesMetadata();
        $this->assertCount(30, $allAttributesMetadata, "Invalid number of attributes returned.");

        $allAttributesMetadata2 = $this->service->getAllAttributesMetadata();
        $this->assertEquals($allAttributesMetadata, $allAttributesMetadata2);

        $allAttributesMetadata3 = $this->service2->getAllAttributesMetadata();
        $this->assertEquals($allAttributesMetadata, $allAttributesMetadata3);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetCustomerAttributeMetadata()
    {
        // Expect these attributes to exist but do not check the value
        $expectAttrsWOutVals = ['created_at', 'updated_at'];

        // Expect these attributes to exist and check the value - values come from _files/customer.php
        $expectAttrsWithVals = [
            'id' => 1,
            'website_id' => 1,
            'store_id' => 1,
            'group_id' => 1,
            'prefix' => 'Mr.',
            'firstname' => 'John',
            'middlename' => 'A',
            'lastname' => 'Smith',
            'suffix' => 'Esq.',
            'email' => 'customer@example.com',
            'default_billing' => '1',
            'default_shipping' => '1',
            'disable_auto_group_change' => 0,
            'taxvat' => '12',
            'gender' => 0
        ];

        $customer = $this->customerRepository->getById(1);
        $this->assertNotNull($customer);

        $attributes = $this->extensibleDataObjectConverter->toFlatArray(
            $customer,
            [],
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $this->assertNotEmpty($attributes);

        foreach ($attributes as $attributeCode => $attributeValue) {
            $this->assertNotNull($attributeCode);
            $this->assertNotNull($attributeValue);
            $attributeMetadata = $this->service->getAttributeMetadata($attributeCode);
            $attributeMetadata1 = $this->service->getAttributeMetadata($attributeCode);
            $attributeMetadata2 = $this->service2->getAttributeMetadata($attributeCode);
            $attrMetadataCode = $attributeMetadata->getAttributeCode();
            $attributeMetadata1->getAttributeCode();
            $attributeMetadata2->getAttributeCode();
            $this->assertEquals($attributeMetadata, $attributeMetadata1);
            $this->assertEquals($attributeMetadata, $attributeMetadata2);
            $this->assertSame($attributeCode, $attrMetadataCode);
            if (($key = array_search($attrMetadataCode, $expectAttrsWOutVals)) !== false) {
                unset($expectAttrsWOutVals[$key]);
            } else {
                $this->assertArrayHasKey($attrMetadataCode, $expectAttrsWithVals);
                $this->assertSame(
                    $expectAttrsWithVals[$attrMetadataCode],
                    $attributeValue,
                    "Failed for {$attrMetadataCode}"
                );
                unset($expectAttrsWithVals[$attrMetadataCode]);
            }
        }
        $this->assertEmpty($expectAttrsWOutVals);
        $this->assertEmpty($expectAttrsWithVals);
    }

    public function testGetCustomerAttributeMetadataNoSuchEntity()
    {
        try {
            $this->service->getAttributeMetadata('wrong_attribute_code');
            $this->fail('Expected exception not thrown.');
        } catch (NoSuchEntityException $e) {
            $this->assertEquals(
                'No such entity with entityType = customer, attributeCode = wrong_attribute_code',
                $e->getMessage()
            );
        }

        try {
            $this->service->getAttributeMetadata('wrong_attribute_code');
            $this->fail('Expected exception not thrown.');
        } catch (NoSuchEntityException $e) {
            $this->assertEquals(
                'No such entity with entityType = customer, attributeCode = wrong_attribute_code',
                $e->getMessage()
            );
        }

        try {
            $this->service2->getAttributeMetadata('wrong_attribute_code');
            $this->fail('Expected exception not thrown.');
        } catch (NoSuchEntityException $e) {
            $this->assertEquals(
                'No such entity with entityType = customer, attributeCode = wrong_attribute_code',
                $e->getMessage()
            );
        }
    }

    public function testGetAttributes()
    {
        $formAttributesMetadata = $this->service->getAttributes('adminhtml_customer');
        $this->assertCount(14, $formAttributesMetadata, "Invalid number of attributes for the specified form.");

        /** Check some fields of one attribute metadata */
        $attributeMetadata = $formAttributesMetadata['firstname'];
        $this->assertInstanceOf(\Magento\Customer\Model\Data\AttributeMetadata::class, $attributeMetadata);
        $this->assertEquals('firstname', $attributeMetadata->getAttributeCode(), 'Attribute code is invalid');
        $this->assertNotEmpty($attributeMetadata->getValidationRules(), 'Validation rules are not set');
        $this->assertEquals('1', $attributeMetadata->isSystem(), '"Is system" field value is invalid');
        $this->assertEquals('40', $attributeMetadata->getSortOrder(), 'Sort order is invalid');

        $formAttributesMetadata1 = $this->service->getAttributes('adminhtml_customer');
        $this->assertEquals($formAttributesMetadata, $formAttributesMetadata1);

        $formAttributesMetadata2 = $this->service2->getAttributes('adminhtml_customer');
        $attributeMetadata2 = $formAttributesMetadata2['firstname'];
        $attributeMetadata2->getAttributeCode();
        $attributeMetadata2->getValidationRules();
        $this->assertEquals($formAttributesMetadata, $formAttributesMetadata2);
    }

    protected function tearDown()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /* @var \Magento\Framework\Config\CacheInterface $cache */
        $cache = $objectManager->create(\Magento\Framework\Config\CacheInterface::class);
        $cache->remove('extension_attributes_config');
        CacheCleaner::cleanAll();
    }
}
