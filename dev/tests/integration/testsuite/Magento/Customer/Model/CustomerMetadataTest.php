<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomerMetadataTest extends \PHPUnit_Framework_TestCase
{
    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var CustomerMetadataInterface */
    private $_service;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    private $_extensibleDataObjectConverter;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->configure(
            [
                'Magento\Framework\Api\ExtensionAttribute\Config\Reader' => [
                    'arguments' => [
                        'fileResolver' => ['instance' => 'Magento\Customer\Model\FileResolverStub'],
                    ],
                ],
            ]
        );
        $this->customerRepository = $objectManager->create(
            'Magento\Customer\Api\CustomerRepositoryInterface'
        );
        $this->_service = $objectManager->create('Magento\Customer\Api\CustomerMetadataInterface');
        $this->_extensibleDataObjectConverter = $objectManager->get(
            'Magento\Framework\Api\ExtensibleDataObjectConverter'
        );
    }

    public function testGetCustomAttributesMetadata()
    {
        $customAttributesMetadata = $this->_service->getCustomAttributesMetadata();
        $this->assertCount(0, $customAttributesMetadata, "Invalid number of attributes returned.");
    }

    public function testGetNestedOptionsCustomAttributesMetadata()
    {
        $nestedOptionsAttribute = 'store_id';
        $customAttributesMetadata = $this->_service->getAttributeMetadata($nestedOptionsAttribute);
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
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/attribute_user_defined_custom_attribute.php
     */
    public function testGetCustomAttributesMetadataWithAttributeNamedCustomAttribute()
    {
        $customAttributesMetadata = $this->_service->getCustomAttributesMetadata();
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

        $attributes = $this->_extensibleDataObjectConverter->toFlatArray(
            $customer,
            [],
            '\Magento\Customer\Api\Data\CustomerInterface'
        );
        $this->assertNotEmpty($attributes);

        foreach ($attributes as $attributeCode => $attributeValue) {
            $this->assertNotNull($attributeCode);
            $this->assertNotNull($attributeValue);
            $attributeMetadata = $this->_service->getAttributeMetadata($attributeCode);
            $attrMetadataCode = $attributeMetadata->getAttributeCode();
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
            $this->_service->getAttributeMetadata('wrong_attribute_code');
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
        $formAttributesMetadata = $this->_service->getAttributes('adminhtml_customer');
        $this->assertCount(14, $formAttributesMetadata, "Invalid number of attributes for the specified form.");

        /** Check some fields of one attribute metadata */
        $attributeMetadata = $formAttributesMetadata['firstname'];
        $this->assertInstanceOf('Magento\Customer\Model\Data\AttributeMetadata', $attributeMetadata);
        $this->assertEquals('firstname', $attributeMetadata->getAttributeCode(), 'Attribute code is invalid');
        $this->assertNotEmpty($attributeMetadata->getValidationRules(), 'Validation rules are not set');
        $this->assertEquals('1', $attributeMetadata->isSystem(), '"Is system" field value is invalid');
        $this->assertEquals('40', $attributeMetadata->getSortOrder(), 'Sort order is invalid');
    }
}
