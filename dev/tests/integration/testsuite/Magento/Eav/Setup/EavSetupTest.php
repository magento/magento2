<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
namespace Magento\Eav\Setup;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\TestFramework\Fixture\AppIsolation;

/**
 * Test class for Magento\Eav\Setup\EavSetup.
 * @magentoDbIsolation enabled
 */
class EavSetupTest extends \PHPUnit\Framework\TestCase
{
    /**
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
     * @dataProvider addAttributeDataProvider
     */
    public function testAddAttribute($attributeCode)
    {
        $attributeData = $this->getAttributeData();

        $this->eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode, $attributeData);

        $attribute = $this->eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);

        $this->assertEmpty(array_diff($attributeData, $attribute));
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
     * @dataProvider addAttributeThrowExceptionDataProvider
     */
    #[AppIsolation(true)]
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
     * @dataProvider addInvalidAttributeThrowExceptionDataProvider
     */
    #[AppIsolation(true)]
    public function testAddInvalidAttributeThrowException($attributeCode)
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Please use only letters (a-z or A-Z), ' .
            'numbers (0-9) or underscore (_) in this field,');

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
     * Verify that addAttributeToGroup adds the attribute to the specified group and its attribute set.
     */
    #[AppIsolation(true)]
    public function testAddAttributeToGroup(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var ModuleDataSetupInterface $setup */
        $setup = $objectManager->create(ModuleDataSetupInterface::class);

        $attributeData = $this->getAttributeData();
        $uniqueAttributeName = 'db24abf125674bceabbbd9977bfc4ada';
        $uniqueGroupName = '64a9ed905ca74cbd86533600a3604d43';
        $this->eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, $uniqueAttributeName, $attributeData);
        $this->eavSetup->addAttributeGroup(\Magento\Catalog\Model\Product::ENTITY, 'Default', $uniqueGroupName);

        $entityTypeId = $this->eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $setId = $this->eavSetup->getAttributeSetId(\Magento\Catalog\Model\Product::ENTITY, 'Default');
        $groupId = $this->eavSetup->getAttributeGroupId(\Magento\Catalog\Model\Product::ENTITY, $setId, $uniqueGroupName);
        $attributeId = $this->eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, $uniqueAttributeName);
        $select = $setup->getConnection()->select()
            ->from($setup->getTable('eav_entity_attribute'))
            ->where('entity_type_id = ?', $entityTypeId)
            ->where('attribute_set_id = ?', $setId)
            ->where('attribute_group_id = ?', $groupId)
            ->where('attribute_id = ?', $attributeId);

        // Make sure that the attribute is not assigned to the group already.
        $row = $select->query()->fetch();
        $this->assertFalse($row);

        // The actual action
        $this->eavSetup->addAttributeToGroup($entityTypeId, $setId, $groupId, $attributeId);

        $row = $select->query()->fetch();
        $this->assertIsArray($row);
        $this->assertSame($entityTypeId, $row['entity_type_id']);
        $this->assertSame($setId, $row['attribute_set_id']);
        $this->assertSame($groupId, $row['attribute_group_id']);
        $this->assertSame($attributeId, $row['attribute_id']);
    }

    /**
     * Verify that testRemoveAttributeFromGroup removes the attribute from the specified group and attribute set.
     */
    #[AppIsolation(true)]
    public function testRemoveAttributeFromGroup(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var ModuleDataSetupInterface $setup */
        $setup = $objectManager->create(ModuleDataSetupInterface::class);

        $attributeData = $this->getAttributeData();
        $uniqueAttributeName = 'e0db51820df24b6fb6a181571aab8823';
        $uniqueGroupName = 'c6771ccb1ab549378a87ecd6cb1d1352';
        $this->eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, $uniqueAttributeName, $attributeData);
        $this->eavSetup->addAttributeGroup(\Magento\Catalog\Model\Product::ENTITY, 'Default', $uniqueGroupName);

        $entityTypeId = $this->eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $setId = $this->eavSetup->getAttributeSetId(\Magento\Catalog\Model\Product::ENTITY, 'Default');
        $groupId = $this->eavSetup->getAttributeGroupId(\Magento\Catalog\Model\Product::ENTITY, $setId, $uniqueGroupName);
        $attributeId = $this->eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, $uniqueAttributeName);
        $this->eavSetup->addAttributeToGroup($entityTypeId, $setId, $groupId, $attributeId);

        $select = $setup->getConnection()->select()
            ->from($setup->getTable('eav_entity_attribute'))
            ->where('entity_type_id = ?', $entityTypeId)
            ->where('attribute_set_id = ?', $setId)
            ->where('attribute_group_id = ?', $groupId)
            ->where('attribute_id = ?', $attributeId);

        // Make sure that the attribute is assigned to the group.
        $row = $select->query()->fetch();
        $this->assertIsArray($row);
        $this->assertNotEmpty($row['entity_attribute_id']);

        // The actual action
        $this->eavSetup->removeAttributeFromGroup($entityTypeId, $setId, $groupId, $attributeId);

        // Make sure that the attribute was removed from the group
        $row = $select->query()->fetch();
        $this->assertFalse($row);
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
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $reflection = new \ReflectionObject($this);
        foreach ($reflection->getProperties() as $property) {
            if (!$property->isStatic() && 0 !== strpos($property->getDeclaringClass()->getName(), 'PHPUnit')) {
                $property->setAccessible(true);
                $property->setValue($this, null);
            }
        }
    }
}
