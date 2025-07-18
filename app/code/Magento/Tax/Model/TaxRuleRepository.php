<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model;

use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Tax\Api\Data\TaxRuleInterface;
use Magento\Tax\Api\TaxRuleRepositoryInterface;
use Magento\Tax\Api\Data\TaxRuleSearchResultsInterfaceFactory;
use Magento\Tax\Model\Calculation\RuleFactory;
use Magento\Tax\Model\Calculation\TaxRuleRegistry;
use Magento\Tax\Model\ResourceModel\Calculation\Rule as ResourceRule;
use Magento\Tax\Model\ResourceModel\Calculation\Rule\Collection;
use Magento\Tax\Model\ResourceModel\Calculation\Rule\CollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TaxRuleRepository implements TaxRuleRepositoryInterface
{
    /**
     * @var TaxRuleRegistry
     */
    protected $taxRuleRegistry;

    /**
     * @var TaxRuleSearchResultsInterfaceFactory
     */
    protected $taxRuleSearchResultsFactory;

    /**
     * @var RuleFactory
     */
    protected $taxRuleModelFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ResourceRule
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $joinProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param TaxRuleRegistry $taxRuleRegistry
     * @param TaxRuleSearchResultsInterfaceFactory $searchResultsFactory
     * @param RuleFactory $ruleFactory
     * @param CollectionFactory $collectionFactory
     * @param ResourceRule $resource
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor
     * @param CollectionProcessorInterface | null $collectionProcessor
     */
    public function __construct(
        TaxRuleRegistry $taxRuleRegistry,
        TaxRuleSearchResultsInterfaceFactory $searchResultsFactory,
        RuleFactory $ruleFactory,
        CollectionFactory $collectionFactory,
        ResourceRule $resource,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor,
        ?CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->taxRuleRegistry = $taxRuleRegistry;
        $this->taxRuleSearchResultsFactory = $searchResultsFactory;
        $this->taxRuleModelFactory = $ruleFactory;
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
        $this->joinProcessor = $joinProcessor;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
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
        } catch (AlreadyExistsException $e) {
            throw $e;
        } catch (NoSuchEntityException $e) {
            throw $e;
        } catch (LocalizedException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
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
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->taxRuleSearchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $collection = $this->collectionFactory->create();
        $this->joinProcessor->process($collection);

        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults->setTotalCount($collection->getSize());

        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     * @deprecated 100.2.0
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
     * @deprecated 100.2.0
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

    /**
     * Retrieve collection processor
     *
     * @deprecated 100.2.0
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Magento\Tax\Model\Api\SearchCriteria\TaxRuleCollectionProcessor'
            );
        }
        return $this->collectionProcessor;
    }
}
