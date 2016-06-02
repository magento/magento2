<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Attribute;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

class GroupRepository implements \Magento\Eav\Api\AttributeGroupRepositoryInterface
{
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group
     */
    protected $groupResource;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\GroupFactory
     */
    protected $groupFactory;

    /**
     * @var \Magento\Eav\Api\AttributeSetRepositoryInterface
     */
    protected $setRepository;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory
     */
    protected $groupListFactory;

    /**
     * @var \Magento\Eav\Api\Data\AttributeGroupSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $joinProcessor;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group $groupResource
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupListFactory
     * @param \Magento\Eav\Model\Entity\Attribute\GroupFactory $groupFactory
     * @param \Magento\Eav\Api\AttributeSetRepositoryInterface $setRepository
     * @param \Magento\Eav\Api\Data\AttributeGroupSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group $groupResource,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupListFactory,
        \Magento\Eav\Model\Entity\Attribute\GroupFactory $groupFactory,
        \Magento\Eav\Api\AttributeSetRepositoryInterface $setRepository,
        \Magento\Eav\Api\Data\AttributeGroupSearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor
    ) {
        $this->groupResource = $groupResource;
        $this->groupListFactory = $groupListFactory;
        $this->groupFactory = $groupFactory;
        $this->setRepository = $setRepository;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->joinProcessor = $joinProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Eav\Api\Data\AttributeGroupInterface $group)
    {
        try {
            $this->setRepository->get($group->getAttributeSetId());
        } catch (NoSuchEntityException $ex) {
            throw NoSuchEntityException::singleField('attributeSetId', $group->getAttributeSetId());
        }

        if ($group->getAttributeGroupId()) {
            /** @var \Magento\Eav\Model\Entity\Attribute\Group $existingGroup */
            $existingGroup = $this->groupFactory->create();
            $this->groupResource->load($existingGroup, $group->getAttributeGroupId());

            if (!$existingGroup->getId()) {
                throw NoSuchEntityException::singleField('attributeGroupId', $existingGroup->getId());
            }
            if ($existingGroup->getAttributeSetId() != $group->getAttributeSetId()) {
                throw new StateException(
                    __('Attribute group does not belong to provided attribute set')
                );
            }
        }

        try {
            $this->groupResource->save($group);
        } catch (\Exception $e) {
            throw new StateException(__('Cannot save attributeGroup'));
        }
        return $group;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $attributeSetId = $this->retrieveAttributeSetIdFromSearchCriteria($searchCriteria);
        if (!$attributeSetId) {
            throw InputException::requiredField('attribute_set_id');
        }
        try {
            $this->setRepository->get($attributeSetId);
        } catch (\Exception $exception) {
            throw NoSuchEntityException::singleField('attributeSetId', $attributeSetId);
        }

        $collection = $this->groupListFactory->create();
        $this->joinProcessor->process($collection);
        $collection->setAttributeSetFilter($attributeSetId);
        $collection->setSortOrder();

        if ($attributeGroupCode = $this->retrieveAttributeGroupCodeFromSearchCriteria($searchCriteria)) {
            $collection->addFilter('attribute_group_code', $attributeGroupCode);
        }

        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult;
    }

    /**
     * {@inheritdoc}
     */
    public function get($groupId)
    {
        /** @var \Magento\Eav\Model\Entity\Attribute\Group $group */
        $group = $this->groupFactory->create();
        $this->groupResource->load($group, $groupId);
        if (!$group->getId()) {
            throw new NoSuchEntityException(__('Group with id "%1" does not exist.', $groupId));
        }
        return $group;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Magento\Eav\Api\Data\AttributeGroupInterface $group)
    {
        try {
            $this->groupResource->delete($group);
        } catch (\Exception $e) {
            throw new StateException(
                __(
                    'Cannot delete attributeGroup with id %1',
                    $group->getId()
                ),
                $e
            );
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($groupId)
    {
        $this->delete(
            $this->get($groupId)
        );
        return true;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return null|string
     */
    protected function retrieveAttributeSetIdFromSearchCriteria(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ) {
        foreach ($searchCriteria->getFilterGroups() as $group) {
            foreach ($group->getFilters() as $filter) {
                if ($filter->getField() == 'attribute_set_id') {
                    return $filter->getValue();
                }
            }
        }
        return null;
    }

    /**
     * Retrieve attribute group code
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return null|string
     */
    private function retrieveAttributeGroupCodeFromSearchCriteria(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ) {
        foreach ($searchCriteria->getFilterGroups() as $group) {
            foreach ($group->getFilters() as $filter) {
                if ($filter->getField() === 'attribute_group_code') {
                    return $filter->getValue();
                }
            }
        }
        return null;
    }
}
