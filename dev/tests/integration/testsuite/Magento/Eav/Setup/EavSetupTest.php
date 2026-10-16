<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Eav\Setup;

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test class for Magento\Eav\Setup\EavSetup.
 * @magentoDbIsolation enabled
 */
class EavSetupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Setup model used for EAV attribute operations.
     *
     * @var \Magento\Eav\Setup\EavSetup
     */
    private $eavSetup;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->eavSetup = $objectManager->create(\Magento\Eav\Setup\EavSetup::class);
    }

    /**
     * Verify that add attribute work correct attribute_code.
     *
     * @param string $attributeCode
     *
     *
     */
    #[DataProvider('addAttributeDataProvider')]
    public function testAddAttribute($attributeCode)
    {
        $attributeData = $this->getAttributeData();

        $this->eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode, $attributeData);

        $attribute = $this->eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);

        $this->assertEmpty(array_diff($attributeData, $attribute));
    }

    /**
     * Verify that renamed attribute code cache does not prevent re-adding the original code.
     */
    public function testAddAttributeAfterAttributeCodeRename()
    {
        $entity = \Magento\Catalog\Model\Product::ENTITY;
        $attributeCode = 'eav_setup_rename_test';
        $renamedAttributeCode = 'eav_setup_rename_test_old';

        $this->eavSetup->addAttribute($entity, $attributeCode, $this->getRenameAttributeData());
        $originalId = (int)$this->eavSetup->getAttributeId($entity, $attributeCode);

        $this->eavSetup->updateAttribute($entity, $attributeCode, ['attribute_code' => $renamedAttributeCode]);
        $this->eavSetup->addAttribute($entity, $attributeCode, $this->getRenameAttributeData());

        $renamedId = (int)$this->eavSetup->getAttributeId($entity, $renamedAttributeCode);
        $newId = (int)$this->eavSetup->getAttributeId($entity, $attributeCode);

        $this->assertNotFalse($this->eavSetup->getAttributeId($entity, $renamedAttributeCode));
        $this->assertNotFalse($this->eavSetup->getAttributeId($entity, $attributeCode));
        $this->assertNotSame($renamedId, $newId);
        $this->assertSame($originalId, $renamedId);
    }

    /**
     * Data provider for testAddAttributeThrowException().
     *
     * @return array
     */
    public static function addAttributeDataProvider()
    {
        return [
            ['eav_setup_test'],
            ['characters_59_characters_59_characters_59_characters_59_59_'],
        ];
    }

    /**
     * Verify that add attribute throw exception if attribute_code is not valid.
     *
     * @param string|null $attributeCode
     *
     *
     */
    #[DataProvider('addAttributeThrowExceptionDataProvider')]
    public function testAddAttributeThrowException($attributeCode)
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('An attribute code must not be less than 1 and more than 60 characters.');

        $attributeData = $this->getAttributeData();

        $this->eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode, $attributeData);
    }

    /**
     * Data provider for testAddAttributeThrowException().
     *
     * @return array
     */
    public static function addAttributeThrowExceptionDataProvider()
    {
        return [
            [null],
            [''],
            [' '],
            ['more_than_60_characters_more_than_more_than_60_characters_more'],
        ];
    }

    /**
     * Verify that add attribute throw exception if attribute_code is not valid.
     *
     * @param string|null $attributeCode
     *
     *
     */
    #[DataProvider('addInvalidAttributeThrowExceptionDataProvider')]
    public function testAddInvalidAttributeThrowException($attributeCode)
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage(
            'Please use only letters (a-z or A-Z), numbers (0-9) or underscore (_) in this field,'
        );

        $attributeData = $this->getAttributeData();
        $this->eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode, $attributeData);
    }
    /**
     * Data provider for testAddInvalidAttributeThrowException().
     *
     * @return array
     */
    public static function addInvalidAttributeThrowExceptionDataProvider()
    {
        return [
            ['1first_character_is_not_letter'],
            ['attribute.with.dots'],
        ];
    }

    /**
     * Get simple attribute data.
     */
    private function getAttributeData()
    {
        $attributeData = [
            'type' => 'varchar',
            'backend' => '',
            'frontend' => '',
            'label' => 'Eav Setup Test',
            'input' => 'text',
            'class' => '',
            'source' => '',
            'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
            'visible' => 0,
            'required' => 0,
            'user_defined' => 1,
            'default' => 'none',
            'searchable' => 0,
            'filterable' => 0,
            'comparable' => 0,
            'visible_on_front' => 0,
            'unique' => 0,
            'apply_to' => 'category',
        ];

        return $attributeData;
    }

    /**
     * Get attribute data for rename regression test.
     */
    private function getRenameAttributeData()
    {
        $attributeData = [
            'type' => 'varchar',
            'backend' => '',
            'frontend' => '',
            'label' => 'Eav Setup Rename Test',
            'input' => 'text',
            'class' => '',
            'source' => '',
            'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
            'visible' => 0,
            'required' => 0,
            'user_defined' => 1,
            'default' => 'none',
            'searchable' => 0,
            'filterable' => 0,
            'comparable' => 0,
            'visible_on_front' => 0,
            'unique' => 0,
            'apply_to' => 'category',
        ];

        return $attributeData;
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $reflection = new \ReflectionObject($this);
        foreach ($reflection->getProperties() as $property) {
            if (!$property->isStatic() && 0 !== strpos($property->getDeclaringClass()->getName(), 'PHPUnit')) {
                $property->setValue($this, null);
            }
        }
    }
}
