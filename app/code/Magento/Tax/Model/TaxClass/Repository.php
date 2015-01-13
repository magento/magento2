<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\TaxClass;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Model\Exception as ModelException;
use Magento\Tax\Api\Data\TaxClassInterface;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Model\ClassModelRegistry;
use Magento\Tax\Model\Resource\TaxClass\Collection as TaxClassCollection;
use Magento\Tax\Model\Resource\TaxClass\CollectionFactory as TaxClassCollectionFactory;

class Repository implements \Magento\Tax\Api\TaxClassRepositoryInterface
{
    /**
     * @var TaxClassCollectionFactory
     */
    protected $taxClassCollectionFactory;

    /**
     * @var \Magento\Tax\Api\Data\TaxClassSearchResultsDataBuilder
     */
    protected $searchResultsBuilder;

    /**
     * @var ClassModelRegistry
     */
    protected $classModelRegistry;

    const CLASS_ID_NOT_ALLOWED = 'class_id is not expected for this request.';

    /**
     * Search Criteria Builder
     *
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Filter Builder
     *
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Tax\Model\Resource\TaxClass
     */
    protected $taxClassResource;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param TaxClassCollectionFactory $taxClassCollectionFactory
     * @param \Magento\Tax\Api\Data\TaxClassSearchResultsDataBuilder $searchResultsBuilder
     * @param ClassModelRegistry $classModelRegistry
     * @param \Magento\Tax\Model\Resource\TaxClass $taxClassResource
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        TaxClassCollectionFactory $taxClassCollectionFactory,
        \Magento\Tax\Api\Data\TaxClassSearchResultsDataBuilder $searchResultsBuilder,
        ClassModelRegistry $classModelRegistry,
        \Magento\Tax\Model\Resource\TaxClass $taxClassResource
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->taxClassCollectionFactory = $taxClassCollectionFactory;
        $this->searchResultsBuilder = $searchResultsBuilder;
        $this->classModelRegistry = $classModelRegistry;
        $this->taxClassResource = $taxClassResource;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Tax\Api\Data\TaxClassInterface $taxClass)
    {
        if ($taxClass->getClassId()) {
            $originalTaxClassModel = $this->get($taxClass->getClassId());

            /* should not be allowed to switch the tax class type */
            if ($originalTaxClassModel->getClassType() !== $taxClass->getClassType()) {
                throw new InputException('Updating classType is not allowed.');
            }
        }
        $this->validateTaxClassData($taxClass);
        try {
            $this->taxClassResource->save($taxClass);
        } catch (ModelException $e) {
            if (strpos($e->getMessage(), \Magento\Tax\Model\Resource\TaxClass::UNIQUE_TAX_CLASS_MSG) !== false) {
                throw new InputException(
                    'A class with the same name already exists for ClassType %classType.',
                    ['classType' => $taxClass->getClassType()]
                );
            } else {
                throw $e;
            }
        }
        $this->classModelRegistry->registerTaxClass($taxClass);
        return $taxClass->getClassId();
    }

    /**
     * {@inheritdoc}
     */
    public function get($taxClassId)
    {
        return $this->classModelRegistry->retrieve($taxClassId);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Magento\Tax\Api\Data\TaxClassInterface $taxClass)
    {
        $taxClassId = $taxClass->getClassId();
        try {
            $this->taxClassResource->delete($taxClass);
        } catch (CouldNotDeleteException $e) {
            throw $e;
        } catch (\Exception $e) {
            return false;
        }
        $this->classModelRegistry->remove($taxClassId);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($taxClassId)
    {
        $taxClassModel = $this->get($taxClassId);
        return $this->delete($taxClassModel);
    }

    /**
     * Validate TaxClass Data
     *
     * @param \Magento\Tax\Api\Data\TaxClassInterface $taxClass
     * @return void
     * @throws InputException
     */
    protected function validateTaxClassData(\Magento\Tax\Api\Data\TaxClassInterface $taxClass)
    {
        $exception = new InputException();

        if (!\Zend_Validate::is(trim($taxClass->getClassName()), 'NotEmpty')) {
            $exception->addError(InputException::REQUIRED_FIELD, ['fieldName' => TaxClassInterface::KEY_NAME]);
        }

        $classType = $taxClass->getClassType();
        if (!\Zend_Validate::is(trim($classType), 'NotEmpty')) {
            $exception->addError(InputException::REQUIRED_FIELD, ['fieldName' => TaxClassInterface::KEY_TYPE]);
        } elseif ($classType !== TaxClassManagementInterface::TYPE_CUSTOMER
            && $classType !== TaxClassManagementInterface::TYPE_PRODUCT
        ) {
            $exception->addError(
                InputException::INVALID_FIELD_VALUE,
                ['fieldName' => TaxClassInterface::KEY_TYPE, 'value' => $classType]
            );
        }

        if ($exception->wasErrorAdded()) {
            throw $exception;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $this->searchResultsBuilder->setSearchCriteria($searchCriteria);
        /** @var TaxClassCollection $collection */
        $collection = $this->taxClassCollectionFactory->create();
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        $this->searchResultsBuilder->setTotalCount($collection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        /** @var SortOrder $sortOrder */
        if ($sortOrders) {
            foreach ($searchCriteria->getSortOrders() as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SearchCriteria::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $this->searchResultsBuilder->setItems($collection->getItems());
        return $this->searchResultsBuilder->create();
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * TODO: This method duplicates functionality of search methods in other services and should be refactored.
     *
     * @param FilterGroup $filterGroup
     * @param TaxClassCollection $collection
     * @return void
     */
    protected function addFilterGroupToCollection(FilterGroup $filterGroup, TaxClassCollection $collection)
    {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $fields[] = $filter->getField();
            $conditions[] = [$condition => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
    }
}
