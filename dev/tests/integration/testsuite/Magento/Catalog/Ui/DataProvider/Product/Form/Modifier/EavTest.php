<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Model\AttributeSetRepository;
use Magento\TestFramework\Eav\Model\GetAttributeGroupByName;
use Magento\TestFramework\Eav\Model\ResourceModel\GetEntityIdByAttributeId;

/**
 * Provides tests for eav modifier used in products admin form data provider.
 *
 * @magentoDbIsolation enabled
 */
class EavTest extends AbstractEavTest
{
    /**
     * @var GetAttributeGroupByName
     */
    private $attributeGroupByName;

    /**
     * @var GetEntityIdByAttributeId
     */
    private $getEntityIdByAttributeId;

    /**
     * @var AttributeSetRepository
     */
    private $setRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->attributeGroupByName = $this->objectManager->get(GetAttributeGroupByName::class);
        $this->getEntityIdByAttributeId = $this->objectManager->get(GetEntityIdByAttributeId::class);
        $this->setRepository = $this->objectManager->get(AttributeSetRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_text_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @dataProvider modifyMetaWithAttributeProvider
     * @param string $groupName
     * @param string $groupCode
     * @param string $attributeCode
     * @param array $attributeMeta
     * @return void
     */
    public function testModifyMetaWithAttributeInGroups(
        string $groupName,
        string $groupCode,
        string $attributeCode,
        array $attributeMeta
    ): void {
        $attributeGroup = $this->attributeGroupByName->execute($this->defaultSetId, $groupName);
        $groupId = $attributeGroup ? $attributeGroup->getAttributeGroupId() : 'ynode-1';
        $data = [
            'attributes' => [
                [$this->attributeRepository->get($attributeCode)->getAttributeId(), $groupId, 1],
            ],
            'groups' => [
                [$groupId, $groupName, 1],
            ],
        ];
        $this->prepareAttributeSet($data);
        $this->locatorMock->expects($this->any())->method('getProduct')->willReturn($this->getProduct());
        $actualMeta = $this->eavModifier->modifyMeta([]);
        $expectedMeta = $this->addMetaNesting($attributeMeta, $groupCode, $attributeCode);
        $this->prepareDataForComparison($actualMeta, $expectedMeta);
        $this->assertEquals($expectedMeta, $actualMeta);
    }

    /**
     * @return array
     */
    public function modifyMetaWithAttributeProvider(): array
    {
        $textAttributeMeta = [
            'dataType' => 'textarea',
            'formElement' => 'textarea',
            'visible' => '1',
            'required' => '0',
            'label' => 'Text Attribute',
            'code' => 'text_attribute',
            'source' => 'content',
            'scopeLabel' => '[GLOBAL]',
            'globalScope' => true,
            'sortOrder' => '__placeholder__',
            'componentType' => 'field',
        ];
        $urlKeyAttributeMeta = [
            'dataType' => 'text',
            'formElement' => 'input',
            'visible' => '1',
            'required' => '0',
            'label' => 'URL Key',
            'code' => 'url_key',
            'source' => 'image-management',
            'scopeLabel' => '[STORE VIEW]',
            'globalScope' => false,
            'sortOrder' =>  '__placeholder__',
            'componentType' => 'field',
        ];

        return [
            'new_attribute_in_existing_group' => [
                'group_name' => 'Content',
                'group_code' => 'content',
                'attribute_code' => 'text_attribute',
                'attribute_meta' => $textAttributeMeta,
            ],
            'new_attribute_in_new_group' => [
                'group_name' => 'Test',
                'group_code' => 'test',
                'attribute_code' => 'text_attribute',
                'attribute_meta' => array_merge($textAttributeMeta, ['source' => 'test']),
            ],
            'old_attribute_moved_to_existing_group' => [
                'group_name' => 'Images',
                'group_code' => 'image-management',
                'attribute_code' => 'url_key',
                'attribute_meta' => $urlKeyAttributeMeta,
            ],
            'old_attribute_moved_to_new_group' => [
                'group_name' => 'Test',
                'group_code' => 'test',
                'attribute_code' => 'url_key',
                'attribute_meta' => array_merge($urlKeyAttributeMeta, ['source' => 'test']),
            ],
        ];
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @return void
     */
    public function testModifyMetaWithChangedGroupSorting(): void
    {
        $contentGroupId = $this->attributeGroupByName->execute($this->defaultSetId, 'Content')
            ->getAttributeGroupId();
        $imagesGroupId = $this->attributeGroupByName->execute($this->defaultSetId, 'Images')
            ->getAttributeGroupId();
        $additional = ['groups' => [[$contentGroupId, 'Content', 2], [$imagesGroupId, 'Images', 1]]];
        $this->prepareAttributeSet($additional);
        $this->locatorMock->expects($this->any())->method('getProduct')->willReturn($this->getProduct());
        $actualMeta = $this->eavModifier->modifyMeta([]);
        $groupCodes = ['image-management', 'content'];
        $groups = array_filter(
            array_keys($actualMeta),
            function ($group) use ($groupCodes) {
                return in_array($group, $groupCodes);
            }
        );
        $this->assertEquals($groupCodes, $groups);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @return void
     */
    public function testModifyMetaWithRemovedGroup(): void
    {
        $designAttributes = ['page_layout', 'options_container', 'custom_layout_update'];
        $designGroupId =$this->attributeGroupByName->execute($this->defaultSetId, 'Design')
            ->getAttributeGroupId();
        $additional = ['removeGroups' => [$designGroupId]];
        $this->prepareAttributeSet($additional);
        $this->locatorMock->expects($this->any())->method('getProduct')->willReturn($this->getProduct());
        $actualMeta = $this->eavModifier->modifyMeta([]);
        $this->assertArrayNotHasKey('design', $actualMeta, 'Group "Design" still visible.');
        $this->assertEmpty(
            array_intersect($designAttributes, $this->getUsedAttributes($actualMeta)),
            'Attributes from "Design" group still visible.'
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @return void
     */
    public function testModifyMetaWithRemovedAttribute(): void
    {
        $attributeId = (int)$this->attributeRepository->get('meta_description')->getAttributeId();
        $entityAttributeId = $this->getEntityIdByAttributeId->execute($this->defaultSetId, $attributeId);
        $additional = ['not_attributes' => [$entityAttributeId]];
        $this->prepareAttributeSet($additional);
        $this->locatorMock->expects($this->any())->method('getProduct')->willReturn($this->getProduct());
        $actualMeta = $this->eavModifier->modifyMeta([]);
        $this->assertArrayNotHasKey('meta_description', $this->getUsedAttributes($actualMeta));
    }

    /**
     * Updates default attribute set.
     *
     * @param array $additional
     * @return void
     */
    private function prepareAttributeSet(array $additional): void
    {
        $set = $this->setRepository->get($this->defaultSetId);
        $data = [
            'attributes' => [],
            'groups' => [],
            'not_attributes' => [],
            'removeGroups' => [],
            'attribute_set_name' => 'Default',
        ];
        $set->organizeData(array_merge($data, $additional));
        $this->setRepository->save($set);
    }
}
