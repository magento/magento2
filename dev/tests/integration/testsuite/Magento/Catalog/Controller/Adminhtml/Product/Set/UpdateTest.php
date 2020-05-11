<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product\Set;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Api\AttributeManagementInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeGroupInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\TestFramework\Eav\Model\GetAttributeSetByName;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Test update attribute set.
 */
class UpdateTest extends AbstractBackendController
{
    /**
     * @var Json
     */
    private $json;

    /**
     * @var AttributeSetRepositoryInterface
     */
    private $attributeSetRepository;

    /**
     * @var AttributeManagementInterface
     */
    private $attributeManagement;

    /**
     * @var CollectionFactory
     */
    private $attributeGroupCollectionFactory;

    /**
     * @var GetAttributeSetByName
     */
    private $getAttributeSetByName;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->json = $this->_objectManager->get(Json::class);
        $this->attributeSetRepository = $this->_objectManager->get(AttributeSetRepositoryInterface::class);
        $this->attributeManagement = $this->_objectManager->get(AttributeManagementInterface::class);
        $this->attributeGroupCollectionFactory = $this->_objectManager->get(CollectionFactory::class);
        $this->getAttributeSetByName = $this->_objectManager->get(GetAttributeSetByName::class);
    }

    /**
     * Test that name of attribute set will update/change correctly.
     *
     * @magentoDataFixture Magento/Catalog/_files/attribute_set_based_on_default.php
     *
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testUpdateAttributeSetName(): void
    {
        $attributeSet = $this->getAttributeSetByName->execute('new_attribute_set');
        $currentAttrSetName = $attributeSet->getAttributeSetName();
        $this->assertNotNull($attributeSet);
        $postData = $this->prepareDataToRequest($attributeSet);
        $updateName = 'New attribute set name';
        $postData['attribute_set_name'] = $updateName;
        $this->performRequest((int)$attributeSet->getAttributeSetId(), $postData);
        $this->assertSessionMessages(
            $this->equalTo([(string)__('You saved the attribute set.')]),
            MessageInterface::TYPE_SUCCESS
        );
        $updatedAttributeSet = $this->attributeSetRepository->get((int)$attributeSet->getAttributeSetId());
        $this->assertEquals($updateName, $updatedAttributeSet->getAttributeSetName());
        $updatedAttributeSet->setAttributeSetName($currentAttrSetName);
        $this->attributeSetRepository->save($updatedAttributeSet);
    }

    /**
     * Test add new group to custom attribute set.
     *
     * @magentoDataFixture Magento/Catalog/_files/attribute_set_based_on_default.php
     *
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testUpdateAttributeSetWithNewGroup(): void
    {
        $currentAttrSet = $this->getAttributeSetByName->execute('new_attribute_set');
        $this->assertNotNull($currentAttrSet);
        $attrSetId = (int)$currentAttrSet->getAttributeSetId();
        $currentAttrGroups = $this->getAttributeSetGroupCollection($attrSetId)->getItems();
        $newGroupName = 'Test attribute group name';
        $newGroupSortOrder = 11;
        $postData = $this->prepareDataToRequest($currentAttrSet);
        $postData['groups'][] = [
            null,
            $newGroupName,
            $newGroupSortOrder,
        ];
        $this->performRequest($attrSetId, $postData);
        $this->assertSessionMessages(
            $this->equalTo([(string)__('You saved the attribute set.')]),
            MessageInterface::TYPE_SUCCESS
        );
        $updatedAttrGroups = $this->getAttributeSetGroupCollection($attrSetId)->getItems();
        $diffGroups = array_diff_key($updatedAttrGroups, $currentAttrGroups);
        $this->assertCount(1, $diffGroups);
        /** @var AttributeGroupInterface $newGroup */
        $newGroup = reset($diffGroups);
        $this->assertEquals($newGroupName, $newGroup->getAttributeGroupName());
        $this->assertEquals($newGroupSortOrder, $newGroup->getSortOrder());
    }

    /**
     * Test delete custom group from custom attribute set.
     *
     * @magentoDataFixture Magento/Catalog/_files/attribute_set_based_on_default_with_custom_group.php
     *
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testDeleteCustomGroupFromCustomAttributeSet(): void
    {
        $testGroupName = 'Test attribute group name';
        $currentAttrSet = $this->getAttributeSetByName->execute('new_attribute_set');
        $this->assertNotNull($currentAttrSet);
        $attrSetId = (int)$currentAttrSet->getAttributeSetId();
        $currentAttrGroupsCollection = $this->getAttributeSetGroupCollection($attrSetId);
        $customGroup = $currentAttrGroupsCollection->getItemByColumnValue(
            AttributeGroupInterface::GROUP_NAME,
            $testGroupName
        );
        $this->assertNotNull($customGroup);
        $postData = $this->prepareDataToRequest($currentAttrSet);
        $postData['removeGroups'] = [
            $customGroup->getAttributeGroupId()
        ];
        $this->performRequest($attrSetId, $postData);
        $this->assertSessionMessages(
            $this->equalTo([(string)__('You saved the attribute set.')]),
            MessageInterface::TYPE_SUCCESS
        );
        $updatedAttrGroups = $this->getAttributeSetGroupCollection($attrSetId)->getItems();
        $diffGroups = array_diff_key($currentAttrGroupsCollection->getItems(), $updatedAttrGroups);
        $this->assertCount(1, $diffGroups);
        /** @var AttributeGroupInterface $deletedGroup */
        $deletedGroup = reset($diffGroups);
        $this->assertEquals($testGroupName, $deletedGroup->getAttributeGroupName());
    }

    /**
     * Process attribute set save request.
     *
     * @param int $attributeSetId
     * @param array $postData
     * @return void
     */
    private function performRequest(int $attributeSetId, array $postData = []): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(
            'data',
            $this->json->serialize($postData)
        );
        $this->dispatch('backend/catalog/product_set/save/id/' . $attributeSetId);
    }

    /**
     * Prepare default data to request from attribute set.
     *
     * @param AttributeSetInterface $attributeSet
     * @return array
     */
    private function prepareDataToRequest(AttributeSetInterface $attributeSet): array
    {
        $result = [
            'attribute_set_name' => $attributeSet->getAttributeSetName(),
            'removeGroups' => [],
            'not_attributes' => [],
        ];
        $groups = $attributes = [];
        /** @var AttributeGroupInterface $group */
        foreach ($this->getAttributeSetGroupCollection((int)$attributeSet->getAttributeSetId()) as $group) {
            $groups[] = [
                $group->getAttributeGroupId(),
                $group->getAttributeGroupName(),
                $group->getSortOrder(),
            ];
        }
        $attributeSetAttributes = $this->attributeManagement->getAttributes(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeSet->getAttributeSetId()
        );
        foreach ($attributeSetAttributes as $attribute) {
            $attributes[] = [
                $attribute->getAttributeId(),
                $attribute->getAttributeGroupId(),
                $attribute->getSortOrder(),
            ];
        }
        $result['groups'] = $groups;
        $result['attributes'] = $attributes;

        return $result;
    }

    /**
     * Build attribute set groups collection by attribute set id.
     *
     * @param int $attributeSetId
     * @return Collection
     */
    private function getAttributeSetGroupCollection(int $attributeSetId): Collection
    {
        $groupCollection = $this->attributeGroupCollectionFactory->create();
        $groupCollection->setAttributeSetFilter($attributeSetId);

        return $groupCollection;
    }
}
