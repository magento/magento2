<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Attribute;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeGroupInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set as AttributeSetResource;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Eav\Model\GetAttributeGroupByName;
use Magento\TestFramework\Eav\Model\ResourceModel\GetEntityIdByAttributeId;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Provides tests for attribute set model saving.
 *
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SetTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var AttributeSetRepositoryInterface
     */
    private $setRepository;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var AttributeSetResource
     */
    private $attributeSetResource;

    /**
     * @var CollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @var int
     */
    private $defaultSetId;

    /**
     * @var GetAttributeGroupByName
     */
    private $attributeGroupByName;

    /**
     * @var GetEntityIdByAttributeId
     */
    private $getEntityIdByAttributeId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->setRepository = $this->objectManager->get(AttributeSetRepositoryInterface::class);
        $this->attributeRepository = $this->objectManager->create(ProductAttributeRepositoryInterface::class);
        $this->config = $this->objectManager->get(Config::class);
        $this->defaultSetId = (int)$this->config->getEntityType(Product::ENTITY)->getDefaultAttributeSetId();
        $this->attributeSetResource = $this->objectManager->get(AttributeSetResource::class);
        $this->attributeCollectionFactory = $this->objectManager->get(CollectionFactory ::class);
        $this->attributeGroupByName = $this->objectManager->get(GetAttributeGroupByName::class);
        $this->getEntityIdByAttributeId = $this->objectManager->get(GetEntityIdByAttributeId::class);
    }

    /**
     * @magentoDataFixture Magento/Eav/_files/attribute_with_options.php
     * @dataProvider addAttributeToSetDataProvider
     * @param string $groupName
     * @param string $attributeCode
     * @return void
     */
    public function testSaveWithGroupsAndAttributes(string $groupName, string $attributeCode): void
    {
        $set = $this->setRepository->get($this->defaultSetId);
        $attributeGroup = $this->getAttributeGroup($groupName);
        $groupId = $attributeGroup ? $attributeGroup->getAttributeGroupId() : 'ynode-1';
        $attributeId = (int)$this->attributeRepository->get($attributeCode)->getAttributeId();
        $additional = [
            'attributes' => [
                [$attributeId, $groupId, 1],
            ],
            'groups' => [
                [$groupId, $groupName, 1],
            ],
        ];
        $set->organizeData($this->getAttributeSetData($additional));
        $this->attributeSetResource->save($set);
        $groupId = $attributeGroup
            ? $attributeGroup->getAttributeGroupId()
            : $this->getAttributeGroup($groupName)->getAttributeGroupId();
        $this->config->clear();
        $setInfo = $this->attributeSetResource->getSetInfo([$attributeId], $this->defaultSetId);
        $expectedInfo = [
            $attributeId => [$this->defaultSetId => ['group_id' => $groupId, 'group_sort' => '1', 'sort' => '1']],
        ];
        $this->assertEquals($expectedInfo, $setInfo);
    }

    /**
     * @return array
     */
    public static function addAttributeToSetDataProvider(): array
    {
        return [
            'add_to_existing_group' => [
                'groupName' => 'Content',
                'attributeCode' => 'zzz',
            ],
            'add_to_new_group' => [
                'groupName' => 'Test',
                'attributeCode' => 'zzz',
            ],
            'move_to_existing_group' => [
                'groupName' => 'Images',
                'attributeCode' => 'description',
            ],
            'move_to_new_group' => [
                'groupName' => 'Test',
                'attributeCode' => 'description',
            ],
        ];
    }

    /**
     * @return void
     */
    public function testSaveWithChangedGroupSorting(): void
    {
        $set = $this->setRepository->get($this->defaultSetId);
        $contentGroupId = $this->getAttributeGroup('Content')->getAttributeGroupId();
        $imagesGroupId = $this->getAttributeGroup('Images')->getAttributeGroupId();
        $additional = [
            'groups' => [
                [$contentGroupId, 'Content', 2],
                [$imagesGroupId, 'Images', 1]
            ]
        ];
        $set->organizeData($this->getAttributeSetData($additional));
        $this->attributeSetResource->save($set);
        $contentGroupSort = $this->getAttributeGroup('Content')->getSortOrder();
        $imagesGroupSort = $this->getAttributeGroup('Images')->getSortOrder();
        $this->assertEquals(2, $contentGroupSort);
        $this->assertEquals(1, $imagesGroupSort);
    }

    /**
     * @return void
     */
    public function testSaveWithRemovedGroup(): void
    {
        $set = $this->setRepository->get($this->defaultSetId);
        $designGroupId = $this->getAttributeGroup('Design')->getAttributeGroupId();
        $additional = [
            'removeGroups' => [$designGroupId],
        ];
        $set->organizeData($this->getAttributeSetData($additional));
        $this->attributeSetResource->save($set);
        $this->assertNull(
            $this->getAttributeGroup('Design'),
            'Group "Design" wan\'t deleted.'
        );
        $unusedSetAttributes = $this->getSetExcludedAttributes((int)$set->getAttributeSetId());
        $designAttributeCodes = ['page_layout', 'options_container', 'custom_layout_update'];
        $this->assertNotEmpty(
            array_intersect($designAttributeCodes, $unusedSetAttributes),
            'Attributes from "Design" group still assigned to attribute set.'
        );
    }

    /**
     * @return void
     */
    public function testSaveWithRemovedAttribute(): void
    {
        $set = $this->setRepository->get($this->defaultSetId);
        $attributeId = (int)$this->attributeRepository->get('meta_description')->getAttributeId();
        $additional = [
            'not_attributes' => [$this->getEntityAttributeId($this->defaultSetId, $attributeId)],
        ];
        $set->organizeData($this->getAttributeSetData($additional));
        $this->attributeSetResource->save($set);
        $this->config->clear();
        $setInfo = $this->attributeSetResource->getSetInfo([$attributeId], $this->defaultSetId);
        $this->assertEmpty($setInfo[$attributeId]);
        $unusedSetAttributes = $this->getSetExcludedAttributes((int)$set->getAttributeSetId());
        $this->assertNotEmpty(
            array_intersect(['meta_description'], $unusedSetAttributes),
            'Attribute still assigned to attribute set.'
        );
    }

    /**
     * Returns attribute set data for saving.
     *
     * @param array $additional
     * @return array
     */
    private function getAttributeSetData(array $additional): array
    {
        $data = [
            'attributes' => [],
            'groups' => [],
            'not_attributes' => [],
            'removeGroups' => [],
            'attribute_set_name' => 'Default',
        ];

        return array_merge($data, $additional);
    }

    /**
     * Returns attribute group by name.
     *
     * @param string $groupName
     * @return AttributeGroupInterface|null
     */
    private function getAttributeGroup(string $groupName): ?AttributeGroupInterface
    {
        return $this->attributeGroupByName->execute($this->defaultSetId, $groupName);
    }

    /**
     * Returns list of unused attributes in attribute set.
     *
     * @param int $setId
     * @return array
     */
    private function getSetExcludedAttributes(int $setId): array
    {
        $collection = $this->attributeCollectionFactory->create()
            ->setExcludeSetFilter($setId);
        $result = $collection->getColumnValues(AttributeInterface::ATTRIBUTE_CODE);

        return $result;
    }

    /**
     * Returns entity attribute id.
     *
     * @param int $setId
     * @param int $attributeId
     * @return int
     */
    private function getEntityAttributeId(int $setId, int $attributeId): int
    {
        return $this->getEntityIdByAttributeId->execute($setId, $attributeId);
    }
}
