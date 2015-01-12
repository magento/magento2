<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model;

use Magento\Eav\Model\Resource\Entity\Attribute\Collection;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

class AttributeRepository implements \Magento\Eav\Api\AttributeRepositoryInterface
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute
     */
    protected $eavResource;

    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\CollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * @var \Magento\Eav\Api\Data\AttributeSearchResultsDataBuilder
     */
    protected $searchResultsBuilder;

    /**
     * @var Entity\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @param Config $eavConfig
     * @param Resource\Entity\Attribute $eavResource
     * @param Resource\Entity\Attribute\CollectionFactory $attributeCollectionFactory
     * @param \Magento\Eav\Api\Data\AttributeSearchResultsDataBuilder $searchResultsBuilder
     * @param Entity\AttributeFactory $attributeFactory
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Resource\Entity\Attribute $eavResource,
        \Magento\Eav\Model\Resource\Entity\Attribute\CollectionFactory $attributeCollectionFactory,
        \Magento\Eav\Api\Data\AttributeSearchResultsDataBuilder $searchResultsBuilder,
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory
    ) {
        $this->eavConfig = $eavConfig;
        $this->eavResource = $eavResource;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->searchResultsBuilder = $searchResultsBuilder;
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Eav\Api\Data\AttributeInterface $attribute)
    {
        try {
            $this->eavResource->save($attribute);
        } catch (\Exception $e) {
            throw new StateException('Cannot save attribute');
        }
        return $attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($entityTypeCode, \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        if (!$entityTypeCode) {
            throw InputException::requiredField('entity_type_code');
        }

        /** @var \Magento\Eav\Model\Resource\Entity\Attribute\Collection $attributeCollection */
        $attributeCollection = $this->attributeCollectionFactory->create();
        $attributeCollection->addFieldToFilter('entity_type_code', ['eq' => $entityTypeCode]);
        $attributeCollection->join(
            ['entity_type' => $attributeCollection->getTable('eav_entity_type')],
            'main_table.entity_type_id = entity_type.entity_type_id',
            []
        );
        $attributeCollection->join(
            ['eav_entity_attribute' => $attributeCollection->getTable('eav_entity_attribute')],
            'main_table.attribute_id = eav_entity_attribute.attribute_id',
            []
        );
        $attributeCollection->join(
            ['additional_table' => $attributeCollection->getTable('catalog_eav_attribute')],
            'main_table.attribute_id = additional_table.attribute_id',
            []
        );
        //Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $attributeCollection);
        }
        /** @var \Magento\Framework\Api\SortOrder $sortOrder */
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $attributeCollection->addOrder(
                $sortOrder->getField(),
                ($sortOrder->getDirection() == SearchCriteriaInterface::SORT_ASC) ? 'ASC' : 'DESC'
            );
        }

        $totalCount = $attributeCollection->getSize();

        // Group attributes by id to prevent duplicates with different attribute sets
        $attributeCollection->addAttributeGrouping();
        $attributeCollection->setCurPage($searchCriteria->getCurrentPage());
        $attributeCollection->setPageSize($searchCriteria->getPageSize());

        $attributes = [];
        /** @var \Magento\Eav\Api\Data\AttributeInterface $attribute */
        foreach ($attributeCollection as $attribute) {
            $attributes[] = $this->get($entityTypeCode, $attribute->getAttributeCode());
        }
        $this->searchResultsBuilder->setSearchCriteria($searchCriteria);
        $this->searchResultsBuilder->setItems($attributes);
        $this->searchResultsBuilder->setTotalCount($totalCount);
        return $this->searchResultsBuilder->create();
    }

    /**
     * {@inheritdoc}
     */
    public function get($entityTypeCode, $attributeCode)
    {
        /** @var \Magento\Eav\Api\Data\AttributeInterface $attribute */
        $attribute = $this->eavConfig->getAttribute($entityTypeCode, $attributeCode);
        if (!$attribute || !$attribute->getAttributeId()) {
            throw new NoSuchEntityException(sprintf(
                'Attribute with attributeCode "%s" does not exist.',
                $attributeCode
            ));
        }
        return $attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Magento\Eav\Api\Data\AttributeInterface $attribute)
    {
        try {
            $this->eavResource->delete($attribute);
        } catch (\Exception $e) {
            throw new StateException('Cannot delete attribute.');
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($attributeId)
    {
        /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
        $attribute = $this->attributeFactory->create();
        $this->eavResource->load($attribute, $attributeId);

        if (!$attribute->getAttributeId()) {
            throw new NoSuchEntityException(sprintf('Attribute with id "%s" does not exist.', $attributeId));
        }

        $this->delete($attribute);
        return true;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Collection $collection
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     */
    private function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        Collection $collection
    ) {
        /** @var \Magento\Framework\Api\Search\FilterGroup $filter */
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $collection->addFieldToFilter(
                $filter->getField(),
                [$condition => $filter->getValue()]
            );
        }
    }
}
