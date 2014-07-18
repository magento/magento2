<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Tax\Service\V1;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Service\V1\Data\SearchCriteria;
use Magento\Framework\Service\V1\Data\Search\FilterGroup;
use Magento\Tax\Model\Calculation\TaxRuleConverter;
use Magento\Tax\Model\Calculation\TaxRuleRegistry;
use Magento\Tax\Service\V1\Data\TaxRule;
use Magento\Tax\Service\V1\Data\TaxRuleBuilder;
use Magento\Tax\Model\Calculation\Rule as TaxRuleModel;
use Magento\Tax\Model\Calculation\RuleFactory as TaxRuleModelFactory;
use Magento\Tax\Model\Resource\Calculation\Rule\Collection;
use Magento\Framework\Model\Exception as ModelException;

/**
 * TaxRuleService implementation.
 */
class TaxRuleService implements TaxRuleServiceInterface
{
    /**
     * Builder for TaxRule data objects.
     *
     * @var TaxRuleBuilder
     */
    protected $taxRuleBuilder;

    /**
     * @var TaxRuleConverter
     */
    protected $converter;

    /**
     * @var TaxRuleRegistry
     */
    protected $taxRuleRegistry;

    /**
     * @var Data\TaxRuleSearchResultsBuilder
     */
    protected $taxRuleSearchResultsBuilder;

    /**
     * @var TaxRuleModelFactory
     */
    protected $taxRuleModelFactory;

    /**
     * @param TaxRuleBuilder $taxRuleBuilder
     * @param TaxRuleConverter $converter
     * @param TaxRuleRegistry $taxRuleRegistry
     * @param Data\TaxRuleSearchResultsBuilder $taxRuleSearchResultsBuilder
     * @param TaxRuleModelFactory $taxRuleModelFactory
     */
    public function __construct(
        TaxRuleBuilder $taxRuleBuilder,
        TaxRuleConverter $converter,
        TaxRuleRegistry $taxRuleRegistry,
        Data\TaxRuleSearchResultsBuilder $taxRuleSearchResultsBuilder,
        TaxRuleModelFactory $taxRuleModelFactory
    ) {
        $this->taxRuleBuilder = $taxRuleBuilder;
        $this->converter = $converter;
        $this->taxRuleRegistry = $taxRuleRegistry;
        $this->taxRuleSearchResultsBuilder = $taxRuleSearchResultsBuilder;
        $this->taxRuleModelFactory = $taxRuleModelFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function createTaxRule(TaxRule $rule)
    {
        $taxRuleModel = $this->saveTaxRule($rule);
        return $this->converter->createTaxRuleDataObjectFromModel($taxRuleModel);
    }

    /**
     * {@inheritdoc}
     */
    public function updateTaxRule(TaxRule $rule)
    {
        // Only update existing tax rules
        $this->taxRuleRegistry->retrieveTaxRule($rule->getId());

        $this->saveTaxRule($rule);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTaxRule($ruleId)
    {
        $ruleModel = $this->taxRuleRegistry->retrieveTaxRule($ruleId);
        $ruleModel->delete();
        $this->taxRuleRegistry->removeTaxRule($ruleId);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxRule($ruleId)
    {
        $taxRuleModel = $this->taxRuleRegistry->retrieveTaxRule($ruleId);
        return $this->converter->createTaxRuleDataObjectFromModel($taxRuleModel);
    }

    /**
     * {@inheritdoc}
     */
    public function searchTaxRules(SearchCriteria $searchCriteria)
    {
        $this->taxRuleSearchResultsBuilder->setSearchCriteria($searchCriteria);
        $collection = $this->taxRuleModelFactory->create()->getCollection();

        //Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        $this->taxRuleSearchResultsBuilder->setTotalCount($collection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            foreach ($sortOrders as $field => $direction) {
                $field = $this->translateField($field);
                $collection->addOrder($field, $direction == SearchCriteria::SORT_ASC ? 'ASC' : 'DESC');
            }
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        $taxRules = [];

        /** @var TaxRuleModel $taxRuleModel */
        foreach ($collection as $taxRuleModel) {
            $taxRule = $this->converter->createTaxRuleDataObjectFromModel($taxRuleModel);
            $taxRules[] = $taxRule;
        }
        $this->taxRuleSearchResultsBuilder->setItems($taxRules);
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
            case TaxRule::ID:
                return 'tax_calculation_rule_id';
            case TaxRule::SORT_ORDER:
                return 'position';
            default:
                return $field;
        }
    }

    /**
     * Save Tax Rule
     *
     * @param TaxRule $rule
     * @return TaxRuleModel
     * @throws InputException
     * @throws ModelException
     */
    protected function saveTaxRule(TaxRule $rule)
    {
        $this->validate($rule);
        $taxRuleModel = $this->converter->createTaxRuleModel($rule);
        try {
            $taxRuleModel->save();
        } catch (ModelException $e) {
            if ($e->getCode() == ModelException::ERROR_CODE_ENTITY_ALREADY_EXISTS) {
                throw new InputException($e->getMessage());
            } else {
                throw $e;
            }
        }
        $this->taxRuleRegistry->registerTaxRule($taxRuleModel);
        return $taxRuleModel;
    }

    /**
     * Validate tax rule
     *
     * @param TaxRule $rule
     * @return void
     * @throws InputException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function validate(TaxRule $rule)
    {
        $exception = new InputException();

        if (!\Zend_Validate::is(trim($rule->getSortOrder()), 'GreaterThan', [-1])) {
            $exception->addError(
                InputException::INVALID_FIELD_MIN_VALUE,
                ['fieldName' => TaxRule::SORT_ORDER, 'value' => $rule->getSortOrder(), 'minValue' => 0]
            );
        }

        if (!\Zend_Validate::is(trim($rule->getPriority()), 'GreaterThan', [-1])) {
            $exception->addError(
                InputException::INVALID_FIELD_MIN_VALUE,
                ['fieldName' => TaxRule::PRIORITY, 'value' => $rule->getPriority(), 'minValue' => 0]
            );
        }

        // Code is required
        if (!\Zend_Validate::is(trim($rule->getCode()), 'NotEmpty')) {
            $exception->addError(InputException::REQUIRED_FIELD, ['fieldName' => TaxRule::CODE]);
        }
        // customer tax class ids is required
        if (($rule->getCustomerTaxClassIds() === null) || !$rule->getCustomerTaxClassIds()) {
            $exception->addError(InputException::REQUIRED_FIELD, ['fieldName' => TaxRule::CUSTOMER_TAX_CLASS_IDS]);
        }
        // product tax class ids is required
        if (($rule->getProductTaxClassIds() === null) || !$rule->getProductTaxClassIds()) {
            $exception->addError(InputException::REQUIRED_FIELD, ['fieldName' => TaxRule::PRODUCT_TAX_CLASS_IDS]);
        }
        // tax rate ids is required
        if (($rule->getTaxRateIds() === null) || !$rule->getTaxRateIds()) {
            $exception->addError(InputException::REQUIRED_FIELD, ['fieldName' => TaxRule::TAX_RATE_IDS]);
        }

        // throw exception if errors were found
        if ($exception->wasErrorAdded()) {
            throw $exception;
        }
    }
}
