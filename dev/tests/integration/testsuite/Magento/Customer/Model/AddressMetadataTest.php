<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\CacheCleaner;

class AddressMetadataTest extends \PHPUnit\Framework\TestCase
{
    /** @var AddressMetadataInterface */
    private $service;

    /** @var AddressMetadataInterface */
    private $serviceTwo;

    protected function setUp()
    {
        CacheCleaner::cleanAll();
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->configure(
            [
                \Magento\Framework\Api\ExtensionAttribute\Config\Reader::class => [
                    'arguments' => [
                        'fileResolver' => ['instance' => \Magento\Customer\Model\FileResolverStub::class],
                    ],
                ],
            ]
        );
        $this->service = $objectManager->create(\Magento\Customer\Api\AddressMetadataInterface::class);
        $this->serviceTwo = $objectManager->create(\Magento\Customer\Api\AddressMetadataInterface::class);
    }

    public function testGetCustomAttributesMetadata()
    {
        $customAttributesMetadata = $this->service->getCustomAttributesMetadata();
        $this->assertCount(0, $customAttributesMetadata, "Invalid number of attributes returned.");

        // Verify the consistency of getCustomAttributeMetadata() function from the 2nd call of the same service
        $customAttributesMetadata2 = $this->service->getCustomAttributesMetadata();
        $this->assertCount(0, $customAttributesMetadata2, "Invalid number of attributes returned.");

        // Verify the consistency of getCustomAttributesMetadata() function from the 2nd service
        $customAttributesMetadata3 = $this->serviceTwo->getCustomAttributesMetadata();
        $this->assertCount(0, $customAttributesMetadata3, "Invalid number of attributes returned.");
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/attribute_user_defined_address_custom_attribute.php
     */
    public function testGetCustomAttributesMetadataWithCustomAttribute()
    {
        $customAttributesMetadata = $this->service->getCustomAttributesMetadata();
        // Verify the consistency of getCustomAttributeMetadata() function from the 2nd call of the same service
        $customAttributesMetadata1 = $this->service->getCustomAttributesMetadata();
        $this->assertEquals(
            $customAttributesMetadata,
            $customAttributesMetadata1,
            'Different custom attribute metadata returned from the 2nd call of the same service'
        );
        // Verify the consistency of getCustomAttributesMetadata() function from the 2nd service
        $customAttributesMetadata2 = $this->serviceTwo->getCustomAttributesMetadata();
        $this->assertEquals(
            $customAttributesMetadata,
            $customAttributesMetadata2,
            'Different custom attribute metadata returned from the 2nd service'
        );

        $customAttributeCodeOne = 'custom_attribute1';
        $customAttributeFound = false;
        $customAttributeCodeTwo = 'custom_attribute2';
        $customAttributesFound = false;
        foreach ($customAttributesMetadata as $attribute) {
            if ($attribute->getAttributeCode() == $customAttributeCodeOne) {
                $customAttributeFound = true;
            }
            if ($attribute->getAttributeCode() == $customAttributeCodeTwo) {
                $customAttributesFound = true;
            }
        }
        if (!$customAttributeFound) {
            $this->fail("Custom attribute declared in the config not found.");
        }
        if (!$customAttributesFound) {
            $this->fail("Custom attributes declared in the config not found.");
        }
        $this->assertCount(2, $customAttributesMetadata, "Invalid number of attributes returned.");

        // Verify the consistency of the custom attribute metadata from two calls of the same service
        // after getAttributeCode was called
        foreach ($customAttributesMetadata1 as $attribute1) {
            $attribute1->getAttributeCode();
        }
        $this->assertEquals(
            $customAttributesMetadata,
            $customAttributesMetadata1,
            'Custom attribute metadata from the same service became different after getAttributeCode was called'
        );

        // Verify the consistency of the custom attribute metadata from two services
        // after getAttributeCode was called
        foreach ($customAttributesMetadata2 as $attribute2) {
            $attribute2->getAttributeCode();
        }
        $this->assertEquals(
            $customAttributesMetadata,
            $customAttributesMetadata2,
            'Custom attribute metadata from two services are different after getAttributeCode was called'
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/attribute_user_defined_address_custom_attribute.php
     */
    public function testGetAllAttributesMetadataWithCustomAttribute()
    {
        $allAttributesMetadata = $this->service->getAllAttributesMetadata();

        // Verify the consistency of getAllAttributesMetadata() function from the 2nd call of the same service
        $allAttributesMetadata2 = $this->service->getAllAttributesMetadata();
        $this->assertEquals(
            $allAttributesMetadata,
            $allAttributesMetadata2,
            'Different attribute metadata returned from the 2nd call of the same service'
        );

        // Verify the consistency of getAllAttributesMetadata() function from the 2nd service
        $allAttributesMetadata3 = $this->serviceTwo->getAllAttributesMetadata();
        $this->assertEquals(
            $allAttributesMetadata,
            $allAttributesMetadata3,
            'Different attribute metadata returned from the 2nd service'
        );
    }

    public function testGetAddressAttributeMetadata()
    {
        $vatValidMetadata = $this->service->getAttributeMetadata('vat_is_valid');
        $this->assertNotNull($vatValidMetadata);
        $this->assertEquals('vat_is_valid', $vatValidMetadata->getAttributeCode());
        $this->assertEquals('text', $vatValidMetadata->getFrontendInput());
        $this->assertEquals('VAT number validity', $vatValidMetadata->getStoreLabel());

        // Verify the consistency of attribute metadata from two calls of the same service
        $vatValidMetadata2 = $this->service->getAttributeMetadata('vat_is_valid');
        $this->assertEquals(
            $vatValidMetadata,
            $vatValidMetadata2,
            'Different attribute metadata returned from the 2nd call of the same service'
        );

        // Verify the consistency of attribute metadata from two services
        $vatValidMetadata3 = $this->serviceTwo->getAttributeMetadata('vat_is_valid');
        $this->assertEquals('vat_is_valid', $vatValidMetadata3->getAttributeCode());
        $this->assertEquals(
            $vatValidMetadata,
            $vatValidMetadata3,
            'Different attribute metadata returned from the 2nd service'
        );
    }

    public function testGetAddressAttributeMetadataNoSuchEntity()
    {
        try {
            $this->service->getAttributeMetadata('1');
            $this->fail('Expected exception not thrown.');
        } catch (NoSuchEntityException $e) {
            $this->assertEquals(
                'No such entity with entityType = customer_address, attributeCode = 1',
                $e->getMessage()
            );
        }

        // Verify the consistency of getAttributeMetadata() function from the 2nd call of the same service
        try {
            $this->service->getAttributeMetadata('1');
            $this->fail('Expected exception not thrown when called the 2nd time.');
        } catch (NoSuchEntityException $e) {
            $this->assertEquals(
                'No such entity with entityType = customer_address, attributeCode = 1',
                $e->getMessage()
            );
        }

        // Verify the consistency of getAttributeMetadata() function from the 2nd service
        try {
            $this->serviceTwo->getAttributeMetadata('1');
            $this->fail('Expected exception not thrown when called with the 2nd service.');
        } catch (NoSuchEntityException $e) {
            $this->assertEquals(
                'No such entity with entityType = customer_address, attributeCode = 1',
                $e->getMessage()
            );
        }
    }

    public function testGetAttributes()
    {
        /** @var \Magento\Customer\Api\Data\ValidationRuleInterfaceFactory $validationRulesFactory */
        $validationRulesFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Api\Data\ValidationRuleInterfaceFactory::class
        );
        $expectedValidationRules = [
            $validationRulesFactory->create(['data' => ['name' => 'max_text_length', 'value' => 255]]),
            $validationRulesFactory->create(['data' => ['name' => 'min_text_length', 'value' => 1]]),
        ];

        // Verify the consistency of getAttributes() function from the 2nd call of the same service
        $formAttributesMetadata = $this->service->getAttributes('customer_address_edit');
        $this->assertCount(15, $formAttributesMetadata, "Invalid number of attributes for the specified form.");
        $formAttributesMetadata1 = $this->service->getAttributes('customer_address_edit');
        $this->assertEquals(
            $formAttributesMetadata,
            $formAttributesMetadata1,
            'Different form attribute metadata returned from the 2nd call of the same service'
        );

        // Verify the consistency of getAttributes() function from the 2nd service
        $formAttributesMetadata2 = $this->serviceTwo->getAttributes('customer_address_edit');
        $this->assertEquals(
            $formAttributesMetadata,
            $formAttributesMetadata2,
            'Different form attribute metadata returned from the 2nd service'
        );

        /** Check some fields of one attribute metadata */
        $attributeMetadata = $formAttributesMetadata['company'];
        $this->assertInstanceOf(\Magento\Customer\Model\Data\AttributeMetadata::class, $attributeMetadata);
        $this->assertEquals('company', $attributeMetadata->getAttributeCode(), 'Attribute code is invalid');
        $validationRules = $attributeMetadata->getValidationRules();
        $this->assertEquals($expectedValidationRules, $validationRules);
        $this->assertEquals('static', $attributeMetadata->getBackendType(), 'Backend type is invalid');
        $this->assertEquals('Company', $attributeMetadata->getFrontendLabel(), 'Frontend label is invalid');
        $vatIdAttributeMetadata = $formAttributesMetadata['vat_id'];
        $this->assertEquals([], $vatIdAttributeMetadata->getOptions());
        $this->assertEquals([], $vatIdAttributeMetadata->getValidationRules());

        // Verify the consistency of form attribute metadata from two calls of the same service
        // after some getters were called
        $attributeMetadata1 = $formAttributesMetadata1['company'];
        $this->assertEquals('company', $attributeMetadata1->getAttributeCode(), 'Attribute code is invalid');
        $this->assertEquals($expectedValidationRules, $attributeMetadata1->getValidationRules());
        $vatIdAttributeMetadata1 = $formAttributesMetadata1['vat_id'];
        $this->assertEquals([], $vatIdAttributeMetadata1->getOptions());
        $this->assertEquals([], $vatIdAttributeMetadata1->getValidationRules());
        $this->assertEquals(
            $formAttributesMetadata,
            $formAttributesMetadata1,
            'Form attribute metadata from the same service became different after some getters were called'
        );

        // Verify the consistency of form attribute metadata from two services
        // after some getters were called
        $attributeMetadata2 = $formAttributesMetadata2['company'];
        $this->assertEquals('company', $attributeMetadata2->getAttributeCode(), 'Attribute code is invalid');
        $this->assertEquals($expectedValidationRules, $attributeMetadata2->getValidationRules());
        $vatIdAttributeMetadata2 = $formAttributesMetadata2['vat_id'];
        $this->assertEquals([], $vatIdAttributeMetadata2->getOptions());
        $this->assertEquals([], $vatIdAttributeMetadata2->getValidationRules());
        $this->assertEquals(
            $formAttributesMetadata,
            $formAttributesMetadata2,
            'Form attribute metadata from two services are different after some getters were called'
        );
    }

    protected function tearDown()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /* @var \Magento\Framework\Config\CacheInterface $cache */
        $cache = $objectManager->create(\Magento\Framework\Config\CacheInterface::class);
        $cache->remove('extension_attributes_config');
    }
}
