<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model;

use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set as AttributeSetResource;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
     * @var \Magento\Eav\Api\Data\AttributeSetSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $joinProcessor;

    /**
     * @param AttributeSetResource $attributeSetResource
     * @param AttributeSetFactory $attributeSetFactory
     * @param CollectionFactory $collectionFactory
     * @param Config $eavConfig
     * @param \Magento\Eav\Api\Data\AttributeSetSearchResultsInterfaceFactory $searchResultFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor
     * @codeCoverageIgnore
     */
    public function __construct(
        AttributeSetResource $attributeSetResource,
        AttributeSetFactory $attributeSetFactory,
        CollectionFactory $collectionFactory,
        EavConfig $eavConfig,
        \Magento\Eav\Api\Data\AttributeSetSearchResultsInterfaceFactory $searchResultFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor
    ) {
        $this->attributeSetResource = $attributeSetResource;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->collectionFactory = $collectionFactory;
        $this->eavConfig = $eavConfig;
        $this->searchResultsFactory = $searchResultFactory;
        $this->joinProcessor = $joinProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function save(AttributeSetInterface $attributeSet)
    {
        try {
            $this->attributeSetResource->save($attributeSet);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('There was an error saving attribute set.'));
        }
        return $attributeSet;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->joinProcessor->process($collection);

        /** The only possible/meaningful search criteria for attribute set is entity type code */
        $entityTypeCode = $this->getEntityTypeCode($searchCriteria);

        if ($entityTypeCode !== null) {
            $collection->setEntityTypeFilter($this->eavConfig->getEntityType($entityTypeCode)->getId());
        }

        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
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
            throw new CouldNotDeleteException(__('Default attribute set can not be deleted'));
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__('There was an error deleting attribute set.'));
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
