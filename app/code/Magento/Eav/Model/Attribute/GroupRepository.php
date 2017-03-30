<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Attribute;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group $groupResource
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupListFactory
     * @param \Magento\Eav\Model\Entity\Attribute\GroupFactory $groupFactory
     * @param \Magento\Eav\Api\AttributeSetRepositoryInterface $setRepository
     * @param \Magento\Eav\Api\Data\AttributeGroupSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor
     * @param CollectionProcessorInterface $collectionProcessor
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group $groupResource,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupListFactory,
        \Magento\Eav\Model\Entity\Attribute\GroupFactory $groupFactory,
        \Magento\Eav\Api\AttributeSetRepositoryInterface $setRepository,
        \Magento\Eav\Api\Data\AttributeGroupSearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->groupResource = $groupResource;
        $this->groupListFactory = $groupListFactory;
        $this->groupFactory = $groupFactory;
        $this->setRepository = $setRepository;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->joinProcessor = $joinProcessor;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
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

        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection $collection */
        $collection = $this->groupListFactory->create();
        $this->joinProcessor->process($collection);

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var \Magento\Eav\Api\Data\AttributeGroupSearchResultsInterface $searchResult */
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
     * Retrieve collection processor
     *
     * @deprecated
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Magento\Eav\Model\Api\SearchCriteria\AttributeGroupCollectionProcessor'
            );
        }
        return $this->collectionProcessor;
    }
}
