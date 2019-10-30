<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Model\Entity\Attribute;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Group;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Eav\Api\AttributeGroupRepositoryInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeGroupInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set as AttributeSetResource;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Provides tests for attribute set model saving.
 */
class SaveTest extends \PHPUnit\Framework\TestCase
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
     * @var AttributeGroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;

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
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->setRepository = $this->objectManager->get(AttributeSetRepositoryInterface::class);
        $this->groupRepository = Bootstrap::getObjectManager()->create(AttributeGroupRepositoryInterface::class);
        $this->config = $this->objectManager->get(Config::class);
        $this->defaultSetId = (int)$this->config->getEntityType(Product::ENTITY)->getDefaultAttributeSetId();
        $this->criteriaBuilder = Bootstrap::getObjectManager()->create(SearchCriteriaBuilder::class);
        $this->attributeSetResource = $this->objectManager->get(AttributeSetResource::class);
        $this->attributeCollectionFactory = $this->objectManager->get(CollectionFactory ::class);
    }

    /**
     * @magentoDataFixture Magento/Eav/_files/attribute_with_options.php
     * @dataProvider addAttributeToSetDataProvider
     * @magentoDbIsolation enabled
     * @param string $groupName
     * @param string $attributeCode
     * @return void
     */
    public function testSaveWithGroupsAndAttributes(string $groupName, string $attributeCode): void
    {
        $set = $this->setRepository->get($this->defaultSetId);
        $groupId = $this->getAttributeGroup($groupName)
            ? $this->getAttributeGroup($groupName)->getAttributeGroupId()
            : 'ynode-1';
        $attributeId = (int)$this->config->getAttribute(Product::ENTITY, $attributeCode)->getAttributeId();
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
        $groupId = $this->getAttributeGroup($groupName)->getAttributeGroupId();
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
    public function addAttributeToSetDataProvider(): array
    {
        return [
            'add_to_existing_group' => [
                'group_name' => 'Content',
                'attribute_code' => 'zzz',
            ],
            'add_to_new_group' => [
                'group_name' => 'Test',
                'attribute_code' => 'zzz',
            ],
            'move_to_existing_group' => [
                'group_name' => 'Images',
                'attribute_code' => 'description',
            ],
            'move_to_new_group' => [
                'group_name' => 'Test',
                'attribute_code' => 'description',
            ],
        ];
    }

    /**
     * @magentoDbIsolation enabled
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
     * @magentoDbIsolation enabled
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
            'Group Design wan\'t deleted.'
        );
        $unusedSetAttributes = $this->getUnusedSetAttributes((int)$set->getAttributeSetId());
        $designAttributeCodes = ['page_layout', 'options_container', 'custom_layout_update'];
        $this->assertNotEmpty(
            array_intersect($designAttributeCodes, $unusedSetAttributes),
            'Attributes from Design group still assigned to attribute set.'
        );
    }

    /**
     * @magentoDbIsolation enabled
     * @return void
     */
    public function testSaveWithRemovedAttribute(): void
    {
        $set = $this->setRepository->get($this->defaultSetId);
        $attributeId = (int)$this->config->getAttribute(Product::ENTITY, 'meta_description')
            ->getAttributeId();
        $additional = [
            'not_attributes' => [$this->getEntityAttributeId($this->defaultSetId, $attributeId)],
        ];
        $set->organizeData($this->getAttributeSetData($additional));
        $this->attributeSetResource->save($set);
        $this->config->clear();
        $setInfo = $this->attributeSetResource->getSetInfo([$attributeId], $this->defaultSetId);
        $this->assertEmpty($setInfo[$attributeId]);
        $unusedSetAttributes = $this->getUnusedSetAttributes((int)$set->getAttributeSetId());
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
     * @return AttributeGroupInterface|Group|null
     */
    private function getAttributeGroup(string $groupName): ?AttributeGroupInterface
    {
        $searchCriteria = $this->criteriaBuilder->addFilter('attribute_group_name', $groupName)
            ->addFilter('attribute_set_id', $this->defaultSetId)
            ->create();
        $result = $this->groupRepository->getList($searchCriteria)->getItems();

        return !empty($result) ? reset($result) : null;
    }

    /**
     * Returns list of unused attributes in attribute set.
     *
     * @param int $setId
     * @return array
     */
    private function getUnusedSetAttributes(int $setId): array
    {
        $result = [];
        $attributesIds = $this->attributeCollectionFactory->create()
            ->setAttributeSetFilter($setId)
            ->getAllIds();
        $collection = $this->attributeCollectionFactory->create()
            ->setAttributesExcludeFilter($attributesIds)
            ->addVisibleFilter();
        /** @var AbstractAttribute $attribute */
        foreach ($collection as $attribute) {
            $result[] = $attribute->getAttributeCode();
        }

        return $result;
    }

    /**
     * @param int|null $setId
     * @param int $attributeId
     * @return int
     */
    private function getEntityAttributeId(?int $setId, int $attributeId): int
    {
        $select = $this->attributeSetResource->getConnection()->select()
            ->from('eav_entity_attribute', ['entity_attribute_id'])
            ->where('attribute_set_id = ?', $setId)
            ->where('attribute_id = ?', $attributeId);

        return (int)$this->attributeSetResource->getConnection()->fetchOne($select);
    }
}
