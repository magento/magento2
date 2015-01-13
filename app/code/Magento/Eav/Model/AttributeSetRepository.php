<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model;

use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Model\Resource\Entity\Attribute\Set as AttributeSetResource;
use Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class AttributeSetRepository implements AttributeSetRepositoryInterface
{
    /**
     * @var AttributeSetResource
     */
    private $attributeSetResource;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var \Magento\Eav\Api\Data\AttributeSetSearchResultsDataBuilder
     */
    private $searchResultsBuilder;

    /**
     * @param AttributeSetResource $attributeSetResource
     * @param AttributeSetFactory $attributeSetFactory
     * @param CollectionFactory $collectionFactory
     * @param Config $eavConfig
     * @param \Magento\Eav\Api\Data\AttributeSetSearchResultsDataBuilder $searchResultBuilder
     */
    public function __construct(
        AttributeSetResource $attributeSetResource,
        AttributeSetFactory $attributeSetFactory,
        CollectionFactory $collectionFactory,
        EavConfig $eavConfig,
        \Magento\Eav\Api\Data\AttributeSetSearchResultsDataBuilder $searchResultBuilder
    ) {
        $this->attributeSetResource = $attributeSetResource;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->collectionFactory = $collectionFactory;
        $this->eavConfig = $eavConfig;
        $this->searchResultsBuilder = $searchResultBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function save(AttributeSetInterface $attributeSet)
    {
        try {
            $this->attributeSetResource->save($attributeSet);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException('There was an error saving attribute set.');
        }
        return $attributeSet;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magento\Eav\Model\Resource\Entity\Attribute\Set\Collection $collection */
        $collection = $this->collectionFactory->create();

        /** The only possible/meaningful search criteria for attribute set is entity type code */
        $entityTypeCode = $this->getEntityTypeCode($searchCriteria);

        if (!is_null($entityTypeCode)) {
            $collection->setEntityTypeFilter($this->eavConfig->getEntityType($entityTypeCode)->getId());
        }

        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        $this->searchResultsBuilder->setSearchCriteria($searchCriteria);
        $this->searchResultsBuilder->setItems($collection->getItems());
        $this->searchResultsBuilder->setTotalCount($collection->getSize());
        return $this->searchResultsBuilder->create();
    }

    /**
     * Retrieve entity type code from search criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return null|string
     */
    protected function getEntityTypeCode(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() == 'entity_type_code') {
                    return $filter->getValue();
                }
            }
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function get($attributeSetId)
    {
        /** @var AttributeSet $attributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $this->attributeSetResource->load($attributeSet, $attributeSetId);

        if (!$attributeSet->getId()) {
            throw NoSuchEntityException::singleField('id', $attributeSetId);
        }
        return $attributeSet;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(AttributeSetInterface $attributeSet)
    {
        try {
            $this->attributeSetResource->delete($attributeSet);
        } catch (\Magento\Framework\Exception\StateException $exception) {
            throw new CouldNotDeleteException('Default attribute set can not be deleted');
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException('There was an error deleting attribute set.');
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($attributeSetId)
    {
        return $this->delete($this->get($attributeSetId));
    }
}
