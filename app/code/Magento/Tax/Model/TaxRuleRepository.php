<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model;

use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\Exception as ModelException;
use Magento\Tax\Api\Data\TaxRuleInterface;
use Magento\Tax\Api\Data\TaxRuleSearchResultsDataBuilder;
use Magento\Tax\Api\TaxRuleRepositoryInterface;
use Magento\Tax\Model\Calculation\RuleFactory;
use Magento\Tax\Model\Calculation\TaxRuleRegistry;
use Magento\Tax\Model\Resource\Calculation\Rule as Resource;
use Magento\Tax\Model\Resource\Calculation\Rule\Collection;
use Magento\Tax\Model\Resource\Calculation\Rule\CollectionFactory;

class TaxRuleRepository implements TaxRuleRepositoryInterface
{
    /**
     * @var TaxRuleRegistry
     */
    protected $taxRuleRegistry;

    /**
     * @var TaxRuleSearchResultsDataBuilder
     */
    protected $taxRuleSearchResultsBuilder;

    /**
     * @var RuleFactory
     */
    protected $taxRuleModelFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Resource
     */
    protected $resource;

    /**
     * @param TaxRuleRegistry $taxRuleRegistry
     * @param TaxRuleSearchResultsDataBuilder $searchResultsBuilder
     * @param RuleFactory $ruleFactory
     * @param CollectionFactory $collectionFactory
     * @param Resource $resource
     */
    public function __construct(
        TaxRuleRegistry $taxRuleRegistry,
        TaxRuleSearchResultsDataBuilder $searchResultsBuilder,
        RuleFactory $ruleFactory,
        CollectionFactory $collectionFactory,
        Resource $resource
    ) {
        $this->taxRuleRegistry = $taxRuleRegistry;
        $this->taxRuleSearchResultsBuilder = $searchResultsBuilder;
        $this->taxRuleModelFactory = $ruleFactory;
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function get($ruleId)
    {
        return $this->taxRuleRegistry->retrieveTaxRule($ruleId);
    }

    /**
     * {@inheritdoc}
     */
    public function save(TaxRuleInterface $rule)
    {
        try {
            $ruleId = $rule->getId();
            if ($ruleId) {
                $this->taxRuleRegistry->retrieveTaxRule($ruleId);
            }
            $this->resource->save($rule);
        } catch (ModelException $e) {
            if ($e->getCode() == ModelException::ERROR_CODE_ENTITY_ALREADY_EXISTS) {
                throw new InputException($e->getMessage());
            } else {
                throw new CouldNotSaveException($e->getMessage());
            }
        } catch (NoSuchEntityException $exception) {
            throw $exception;
        }
        $this->taxRuleRegistry->registerTaxRule($rule);
        return $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(TaxRuleInterface $rule)
    {
        $ruleId = $rule->getId();
        $this->resource->delete($rule);
        $this->taxRuleRegistry->removeTaxRule($ruleId);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($ruleId)
    {
        $rule = $this->taxRuleRegistry->retrieveTaxRule($ruleId);
        return $this->delete($rule);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria)
    {
        $this->taxRuleSearchResultsBuilder->setSearchCriteria($searchCriteria);

        $fields = [];
        $collection = $this->collectionFactory->create();

        //Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
            foreach ($group->getFilters() as $filter) {
                $fields[] = $this->translateField($filter->getField());
            }
        }
        if ($fields) {
            if (in_array('cd.customer_tax_class_id', $fields) || in_array('cd.product_tax_class_id', $fields)) {
                $collection->joinCalculationData('cd');
            }
        }

        $this->taxRuleSearchResultsBuilder->setTotalCount($collection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        /** @var SortOrder $sortOrder */
        if ($sortOrders) {
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $this->translateField($sortOrder->getField()),
                    ($sortOrder->getDirection() == SearchCriteria::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        $this->taxRuleSearchResultsBuilder->setItems($collection->getItems());
        return $this->taxRuleSearchResultsBuilder->create();
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $collection)
    {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $field = $this->translateField($filter->getField());
            $fields[] = $field;
            $conditions[] = [$condition => $filter->getValue()];
            switch ($field) {
                case 'rate.tax_calculation_rate_id':
                    $collection->joinCalculationData('rate');
                    break;

                case 'ctc.customer_tax_class_id':
                    $collection->joinCalculationData('ctc');
                    break;

                case 'ptc.product_tax_class_id':
                    $collection->joinCalculationData('ptc');
                    break;
            }
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    /**
     * Translates a field name to a DB column name for use in collection queries.
     *
     * @param string $field a field name that should be translated to a DB column name.
     * @return string
     */
    protected function translateField($field)
    {
        switch ($field) {
            case "id":
                return 'tax_calculation_rule_id';
            case 'tax_rate_ids':
                return 'tax_calculation_rate_id';
            case 'customer_tax_class_ids':
                return 'cd.customer_tax_class_id';
            case 'product_tax_class_ids':
                return 'cd.product_tax_class_id';
            case 'sort_order':
                return 'position';
            default:
                return $field;
        }
    }
}
