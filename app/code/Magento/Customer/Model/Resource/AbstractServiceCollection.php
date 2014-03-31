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
namespace Magento\Customer\Model\Resource;

use Magento\Core\Model\EntityFactory;
use Magento\Customer\Service\V1\Data\Filter;
use Magento\Customer\Service\V1\Data\FilterBuilder;
use Magento\Customer\Service\V1\Data\SearchCriteria;
use Magento\Customer\Service\V1\Data\SearchCriteriaBuilder;
use Magento\Exception;

/**
 * Base for customer service collections
 */
abstract class AbstractServiceCollection extends \Magento\Data\Collection
{
    /**
     * Filters on specific fields
     *
     * Each filter has the following structure
     * <pre>
     * [
     *     'field'     => $field,
     *     'condition' => $condition,
     * ]
     * </pre>
     * @see addFieldToFilter() for more information on conditions
     *
     * @var array
     */
    protected $fieldFilters = array();

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @param EntityFactory $entityFactory
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        EntityFactory $entityFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($entityFactory);
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Add field filter to collection
     *
     * If $condition integer or string - exact value will be filtered ('eq' condition)
     *
     * If $condition is array - one of the following structures is expected:
     * <pre>
     * - ["from" => $fromValue, "to" => $toValue]
     * - ["eq" => $equalValue]
     * - ["neq" => $notEqualValue]
     * - ["like" => $likeValue]
     * - ["in" => [$inValues]]
     * - ["nin" => [$notInValues]]
     * - ["notnull" => $valueIsNotNull]
     * - ["null" => $valueIsNull]
     * - ["moreq" => $moreOrEqualValue]
     * - ["gt" => $greaterValue]
     * - ["lt" => $lessValue]
     * - ["gteq" => $greaterOrEqualValue]
     * - ["lteq" => $lessOrEqualValue]
     * - ["finset" => $valueInSet]
     * - ["regexp" => $regularExpression]
     * - ["seq" => $stringValue]
     * - ["sneq" => $stringValue]
     * </pre>
     *
     * If non matched - sequential parallel arrays are expected and OR conditions
     * will be built using above mentioned structure.
     *
     * Example:
     * <pre>
     * $field = ['age', 'name'];
     * $condition = [42, ['like' => 'Mage']];
     * </pre>
     * The above would find where age equal to 42 OR name like %Mage%.
     *
     * @param string|array $field
     * @param string|int|array $condition
     * @throws Exception if some error in the input could be detected.
     * @return $this
     */
    public function addFieldToFilter($field, $condition)
    {
        if (is_array($field) && count($field) != count($condition)) {
            throw new Exception('When passing in a field array there must be a matching condition array.');
        }
        $this->fieldFilters[] = array('field' => $field, 'condition' => $condition);
        return $this;
    }

    /**
     * Creates a search criteria DTO based on the array of field filters.
     *
     * @return SearchCriteria
     */
    protected function getSearchCriteria()
    {
        foreach ($this->fieldFilters as $filter) {
            if (!is_array($filter['field'])) {
                // just one field
                $this->searchCriteriaBuilder->addFilter(
                    $this->createFilterData($filter['field'], $filter['condition'])
                );
            } else {
                // array of fields, put filters in array to use 'or' group
                /** @var Filter[] $orGroupFilters */
                $orGroupFilters = array();
                foreach ($filter['field'] as $index => $field) {
                    $orGroupFilters[] = $this->createFilterData($field, $filter['condition'][$index]);
                }
                $this->searchCriteriaBuilder->addOrGroup($orGroupFilters);
            }
        }
        foreach ($this->_orders as $field => $direction) {
            $this->searchCriteriaBuilder->addSortOrder(
                $field,
                $direction == 'ASC' ? SearchCriteria::SORT_ASC : SearchCriteria::SORT_DESC
            );
        }
        $this->searchCriteriaBuilder->setCurrentPage($this->_curPage);
        $this->searchCriteriaBuilder->setPageSize($this->_pageSize);
        return $this->searchCriteriaBuilder->create();
    }

    /**
     * Creates a filter DTO for given field/condition
     *
     * @param string $field Field for new filter
     * @param string|array $condition Condition for new filter.
     * @return Filter
     */
    protected function createFilterData($field, $condition)
    {
        $this->filterBuilder->setField($field);

        if (is_array($condition)) {
            $this->filterBuilder->setValue(reset($condition));
            $this->filterBuilder->setConditionType(key($condition));
        } else {
            // not an array, just use eq as condition type and given value
            $this->filterBuilder->setConditionType('eq');
            $this->filterBuilder->setValue($condition);
        }
        return $this->filterBuilder->create();
    }
}
