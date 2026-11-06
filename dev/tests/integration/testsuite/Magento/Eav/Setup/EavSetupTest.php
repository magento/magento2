<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Eav\Setup;

use Magento\Catalog\Model\Product;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;

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
     * Verify that a rejected attribute code does not make the next valid attribute code fail.
     */
    public function testAddAttributeAfterInvalidAttributeCode(): void
    {
        $attributeData = $this->getAttributeData();
        try {
            $this->eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'attribute.with.dots',
                $attributeData
            );
            $this->fail('An invalid attribute code must be rejected.');
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            $this->assertStringContainsString('Please use only letters', $exception->getMessage());
        }

        $this->eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'eav_setup_test_after_invalid',
            $attributeData
        );

        $this->assertNotEmpty(
            $this->eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'eav_setup_test_after_invalid')
        );
    }

    /**
     * Verify that addAttributeToGroup assigns the attribute to the given group.
     */
    public function testAddAttributeToGroup(): void
    {
        $this->addAttributeWithGroup('eav_setup_test_add_to_group', 'eav_setup_test_add_group');
        $this->assertSame([], $this->getGroupAssignments('eav_setup_test_add_to_group', 'eav_setup_test_add_group'));

        $this->eavSetup->addAttributeToGroup(
            Product::ENTITY,
            'Default',
            'eav_setup_test_add_group',
            'eav_setup_test_add_to_group'
        );

        $this->assertCount(1, $this->getGroupAssignments('eav_setup_test_add_to_group', 'eav_setup_test_add_group'));
    }

    /**
     * Verify that removeAttributeFromGroup removes the assignment when called with codes and names or with IDs.
     *
     * @param string $attributeCode
     * @param string $groupName
     * @param bool $useIds
     */
    #[DataProvider('removeAttributeFromGroupDataProvider')]
    public function testRemoveAttributeFromGroup(string $attributeCode, string $groupName, bool $useIds): void
    {
        $this->addAttributeWithGroup($attributeCode, $groupName);
        $this->eavSetup->addAttributeToGroup(Product::ENTITY, 'Default', $groupName, $attributeCode);
        $this->assertCount(1, $this->getGroupAssignments($attributeCode, $groupName));

        $arguments = [Product::ENTITY, 'Default', $groupName, $attributeCode];
        if ($useIds) {
            $entityTypeId = $this->eavSetup->getEntityTypeId(Product::ENTITY);
            $setId = $this->eavSetup->getAttributeSetId($entityTypeId, 'Default');
            $arguments = [
                $entityTypeId,
                $setId,
                $this->eavSetup->getAttributeGroupId($entityTypeId, $setId, $groupName),
                $this->eavSetup->getAttributeId($entityTypeId, $attributeCode),
            ];
        }
        $this->assertSame($this->eavSetup, $this->eavSetup->removeAttributeFromGroup(...$arguments));

        $this->assertSame([], $this->getGroupAssignments($attributeCode, $groupName));
        $this->assertSame($this->eavSetup, $this->eavSetup->removeAttributeFromGroup(...$arguments));
    }

    /**
     * Data provider for testRemoveAttributeFromGroup().
     *
     * @return array
     */
    public static function removeAttributeFromGroupDataProvider(): array
    {
        return [
            'codes and names' => ['eav_setup_test_remove_by_code', 'eav_setup_test_remove_by_code_group', false],
            'IDs' => ['eav_setup_test_remove_by_id', 'eav_setup_test_remove_by_id_group', true],
        ];
    }

    /**
     * Verify that removeAttributeFromGroup keeps other assignments of the group.
     */
    public function testRemoveAttributeFromGroupKeepsOtherAttributes(): void
    {
        $this->addAttributeWithGroup('eav_setup_test_removed', 'eav_setup_test_shared_group');
        $this->eavSetup->addAttribute(Product::ENTITY, 'eav_setup_test_kept', $this->getAttributeData());
        foreach (['eav_setup_test_removed', 'eav_setup_test_kept'] as $attributeCode) {
            $this->eavSetup->addAttributeToGroup(
                Product::ENTITY,
                'Default',
                'eav_setup_test_shared_group',
                $attributeCode
            );
        }

        $this->eavSetup->removeAttributeFromGroup(
            Product::ENTITY,
            'Default',
            'eav_setup_test_shared_group',
            'eav_setup_test_removed'
        );

        $this->assertSame([], $this->getGroupAssignments('eav_setup_test_removed', 'eav_setup_test_shared_group'));
        $this->assertCount(1, $this->getGroupAssignments('eav_setup_test_kept', 'eav_setup_test_shared_group'));
    }

    /**
     * Verify that an unknown group is not resolved to the default group of the attribute set.
     */
    public function testRemoveAttributeFromUnknownGroupKeepsDefaultGroupAssignment(): void
    {
        $this->eavSetup->addAttribute(Product::ENTITY, 'eav_setup_test_default_group', $this->getAttributeData());
        $entityTypeId = $this->eavSetup->getEntityTypeId(Product::ENTITY);
        $setId = $this->eavSetup->getAttributeSetId($entityTypeId, 'Default');
        $defaultGroupId = $this->eavSetup->getDefaultAttributeGroupId($entityTypeId, $setId);
        $this->eavSetup->addAttributeToGroup($entityTypeId, $setId, $defaultGroupId, 'eav_setup_test_default_group');
        $this->assertCount(1, $this->getGroupAssignments('eav_setup_test_default_group', $defaultGroupId));

        $this->eavSetup->removeAttributeFromGroup(
            Product::ENTITY,
            'Default',
            'eav_setup_test_unknown_group',
            'eav_setup_test_default_group'
        );

        $this->assertCount(1, $this->getGroupAssignments('eav_setup_test_default_group', $defaultGroupId));
    }

    /**
     * Add an attribute and a group to the default product attribute set.
     *
     * @param string $attributeCode
     * @param string $groupName
     */
    private function addAttributeWithGroup(string $attributeCode, string $groupName): void
    {
        $this->eavSetup->addAttribute(Product::ENTITY, $attributeCode, $this->getAttributeData());
        $this->eavSetup->addAttributeGroup(Product::ENTITY, 'Default', $groupName);
    }

    /**
     * Get the rows assigning the attribute to the group of the default product attribute set.
     *
     * @param string $attributeCode
     * @param int|string $groupId
     * @return array
     */
    private function getGroupAssignments(string $attributeCode, int|string $groupId): array
    {
        $entityTypeId = $this->eavSetup->getEntityTypeId(Product::ENTITY);
        $setId = $this->eavSetup->getAttributeSetId($entityTypeId, 'Default');
        $setup = Bootstrap::getObjectManager()->get(ModuleDataSetupInterface::class);
        $select = $setup->getConnection()->select()
            ->from($setup->getTable('eav_entity_attribute'))
            ->where('entity_type_id = ?', $entityTypeId)
            ->where('attribute_set_id = ?', $setId)
            ->where('attribute_group_id = ?', $this->eavSetup->getAttributeGroupId($entityTypeId, $setId, $groupId))
            ->where('attribute_id = ?', $this->eavSetup->getAttributeId($entityTypeId, $attributeCode));

        return $setup->getConnection()->fetchAll($select);
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
                $property->setValue($this, null);
            }
        }
    }
}
