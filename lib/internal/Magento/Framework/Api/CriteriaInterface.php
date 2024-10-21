<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api;

/**
 * Interface CriteriaInterface
 *
 * @api
 */
interface CriteriaInterface
{
    const PART_FIELDS = 'fields';
    const PART_FILTERS = 'filters';
    const PART_ORDERS = 'orders';
    const PART_CRITERIA_LIST = 'criteria_list';
    const PART_LIMIT = 'limit';

    const SORT_ORDER_ASC = 'ASC';
    const SORT_ORDER_DESC = 'DESC';

    /**
     * Get associated Mapper Interface name
     *
     * @return string
     */
    public function getMapperInterfaceName();

    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     * @return void
     */
    public function addField($field, $alias = null);

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
     * </pre>
     *
     * If non matched - sequential parallel arrays are expected and OR conditions
     * will be built using above mentioned structure.
     *
     * Example:
     * <pre>
     * $field = ['age', 'name'];
     * $condition = [42, ['like' => 'Mage']];
     * $type = 'or';
     * </pre>
     * The above would find where age equal to 42 OR name like %Mage%.
     *
     * @param string $name
     * @param string|array $field
     * @param string|int|array $condition
     * @param string $type
     * @throws \Magento\Framework\Exception\LocalizedException if some error in the input could be detected.
     * @return void
     */
    public function addFilter($name, $field, $condition = null, $type = 'and');

    /**
     * self::setOrder() alias
     *
     * @param string $field
     * @param string $direction
     * @param bool $unShift
     * @return void
     */
    public function addOrder($field, $direction = self::SORT_ORDER_DESC, $unShift = false);

    /**
     * Set Query limit
     *
     * @param int $offset
     * @param int $size
     * @return void
     */
    public function setLimit($offset, $size);

    /**
     * Removes field from select
     *
     * @param string|null $field
     * @param bool $isAlias Alias identifier
     * @return void
     */
    public function removeField($field, $isAlias = false);

    /**
     * Removes all fields from select
     *
     * @return void
     */
    public function removeAllFields();

    /**
     * Removes filter by name
     *
     * @param string $name
     * @return void
     */
    public function removeFilter($name);

    /**
     * Removes all filters
     *
     * @return void
     */
    public function removeAllFilters();

    /**
     * Get Criteria objects added to current Composite Criteria
     *
     * @return \Magento\Framework\Api\CriteriaInterface[]
     */
    public function getCriteriaList();

    /**
     * Get list of filters
     *
     * @return string[]
     */
    public function getFilters();

    /**
     * Get ordering criteria
     *
     * @return string[]
     */
    public function getOrders();

    /**
     * Get limit
     * (['offset', 'page'])
     *
     * @return string[]
     */
    public function getLimit();

    /**
     * Retrieve criteria part
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getPart($name, $default = null);

    /**
     * Return all criteria parts as array
     *
     * @return array
     */
    public function toArray();

    /**
     * Reset criteria
     *
     * @return void
     */
    public function reset();
}
