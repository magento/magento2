<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class AddressMetadataTest extends \PHPUnit_Framework_TestCase
{
    /** @var AddressMetadataInterface */
    private $_service;

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
        $this->_service = $objectManager->create('Magento\Customer\Api\AddressMetadataInterface');
    }

    public function testGetCustomAttributesMetadata()
    {
        $customAttributesMetadata = $this->_service->getCustomAttributesMetadata();
        $this->assertCount(0, $customAttributesMetadata, "Invalid number of attributes returned.");
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/attribute_user_defined_address_custom_attribute.php
     */
    public function testGetCustomAttributesMetadataWithAttributeNamedCustomAttribute()
    {
        $customAttributesMetadata = $this->_service->getCustomAttributesMetadata();
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
    }

    public function testGetAddressAttributeMetadata()
    {
        $vatValidMetadata = $this->_service->getAttributeMetadata('vat_is_valid');

        $this->assertNotNull($vatValidMetadata);
        $this->assertEquals('vat_is_valid', $vatValidMetadata->getAttributeCode());
        $this->assertEquals('text', $vatValidMetadata->getFrontendInput());
        $this->assertEquals('VAT number validity', $vatValidMetadata->getStoreLabel());
    }

    public function testGetAddressAttributeMetadataNoSuchEntity()
    {
        try {
            $this->_service->getAttributeMetadata('1');
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
        $formAttributesMetadata = $this->_service->getAttributes('customer_address_edit');
        $this->assertCount(15, $formAttributesMetadata, "Invalid number of attributes for the specified form.");

        /** Check some fields of one attribute metadata */
        $attributeMetadata = $formAttributesMetadata['company'];
        $this->assertInstanceOf('Magento\Customer\Model\Data\AttributeMetadata', $attributeMetadata);
        $this->assertEquals('company', $attributeMetadata->getAttributeCode(), 'Attribute code is invalid');
        $this->assertNotEmpty($attributeMetadata->getValidationRules(), 'Validation rules are not set');
        $this->assertEquals('static', $attributeMetadata->getBackendType(), 'Backend type is invalid');
        $this->assertEquals('Company', $attributeMetadata->getFrontendLabel(), 'Frontend label is invalid');
    }

    protected function tearDown()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /* @var \Magento\Framework\Config\CacheInterface $cache */
        $cache = $objectManager->create('Magento\Framework\Config\CacheInterface');
        $cache->remove('extension_attributes_config');
    }
}
