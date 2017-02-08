<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class AddressMetadataTest extends \PHPUnit_Framework_TestCase
{
    /** @var AddressMetadataInterface */
    private $service;

    /** @var AddressMetadataInterface */
    private $service2;

    protected function setUp()
    {
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
        $this->service2 = $objectManager->create(\Magento\Customer\Api\AddressMetadataInterface::class);
    }

    public function testGetCustomAttributesMetadata()
    {
        $customAttributesMetadata = $this->service->getCustomAttributesMetadata();
        $this->assertCount(0, $customAttributesMetadata, "Invalid number of attributes returned.");
        $customAttributesMetadata2 = $this->service->getCustomAttributesMetadata();
        $this->assertCount(0, $customAttributesMetadata2, "Invalid number of attributes returned.");
        $customAttributesMetadata3 = $this->service2->getCustomAttributesMetadata();
        $this->assertCount(0, $customAttributesMetadata3, "Invalid number of attributes returned.");
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/attribute_user_defined_address_custom_attribute.php
     */
    public function testGetCustomAttributesMetadataWithAttributeNamedCustomAttribute()
    {
        $customAttributesMetadata = $this->service->getCustomAttributesMetadata();
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
        $customAttributesMetadata2 = $this->service->getCustomAttributesMetadata();
        $this->assertEquals($customAttributesMetadata, $customAttributesMetadata2);
        $customAttributesMetadata3 = $this->service2->getCustomAttributesMetadata();
        $this->assertEquals($customAttributesMetadata, $customAttributesMetadata3);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/attribute_user_defined_address_custom_attribute.php
     */
    public function testGetAllAttributesMetadataWithAttributeNamedCustomAttribute()
    {
        $allAttributesMetadata = $this->service->getAllAttributesMetadata();
        $this->assertCount(21, $allAttributesMetadata, "Invalid number of attributes returned.");
        $allAttributesMetadata2 = $this->service->getAllAttributesMetadata();
        $this->assertEquals($allAttributesMetadata, $allAttributesMetadata2);
        $allAttributesMetadata3 = $this->service2->getAllAttributesMetadata();
        $this->assertEquals($allAttributesMetadata, $allAttributesMetadata3);
    }

    public function testGetAddressAttributeMetadata()
    {
        $vatValidMetadata = $this->service->getAttributeMetadata('vat_is_valid');
        $this->assertNotNull($vatValidMetadata);
        $this->assertEquals('vat_is_valid', $vatValidMetadata->getAttributeCode());
        $this->assertEquals('text', $vatValidMetadata->getFrontendInput());
        $this->assertEquals('VAT number validity', $vatValidMetadata->getStoreLabel());

        $vatValidMetadata2 = $this->service->getAttributeMetadata('vat_is_valid');
        $this->assertEquals($vatValidMetadata, $vatValidMetadata2);
        $vatValidMetadata3 = $this->service2->getAttributeMetadata('vat_is_valid');
        $this->assertEquals('vat_is_valid', $vatValidMetadata3->getAttributeCode());
        $this->assertEquals($vatValidMetadata, $vatValidMetadata3);
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

        try {
            $this->service->getAttributeMetadata('1');
            $this->fail('Expected exception not thrown.');
        } catch (NoSuchEntityException $e) {
            $this->assertEquals(
                'No such entity with entityType = customer_address, attributeCode = 1',
                $e->getMessage()
            );
        }

        try {
            $this->service2->getAttributeMetadata('1');
            $this->fail('Expected exception not thrown.');
        } catch (NoSuchEntityException $e) {
            $this->assertEquals(
                'No such entity with entityType = customer_address, attributeCode = 1',
                $e->getMessage()
            );
        }
    }

    public function testGetAttributes()
    {
        $formAttributesMetadata = $this->service->getAttributes('customer_address_edit');
        $this->assertCount(15, $formAttributesMetadata, "Invalid number of attributes for the specified form.");

        /** Check some fields of one attribute metadata */
        $attributeMetadata = $formAttributesMetadata['company'];
        $this->assertInstanceOf(\Magento\Customer\Model\Data\AttributeMetadata::class, $attributeMetadata);
        $this->assertEquals('company', $attributeMetadata->getAttributeCode(), 'Attribute code is invalid');
        $this->assertNotEmpty($attributeMetadata->getValidationRules(), 'Validation rules are not set');
        $this->assertEquals('static', $attributeMetadata->getBackendType(), 'Backend type is invalid');
        $this->assertEquals('Company', $attributeMetadata->getFrontendLabel(), 'Frontend label is invalid');

        $formAttributesMetadata2 = $this->service->getAttributes('customer_address_edit');
        $this->assertEquals($formAttributesMetadata, $formAttributesMetadata2);
        $formAttributesMetadata3 = $this->service2->getAttributes('customer_address_edit');
        $attributeMetadata1 = $formAttributesMetadata3['company'];
        $this->assertEquals('company', $attributeMetadata1->getAttributeCode(), 'Attribute code is invalid');
        $this->assertNotEmpty($attributeMetadata1->getValidationRules(), 'Validation rules are not set');
        $this->assertEquals($formAttributesMetadata, $formAttributesMetadata3);
    }

    protected function tearDown()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /* @var \Magento\Framework\Config\CacheInterface $cache */
        $cache = $objectManager->create(\Magento\Framework\Config\CacheInterface::class);
        $cache->remove('extension_attributes_config');
    }
}
