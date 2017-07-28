<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB;

/**
 * Interface MapperInterface
 * @since 2.0.0
 */
interface MapperInterface
{
    const SORT_ORDER_ASC = 'ASC';

    const SORT_ORDER_DESC = 'DESC';

    /**
     * Map criteria to Select Query Object
     *
     * @param \Magento\Framework\Api\CriteriaInterface $criteria
     * @return Select
     * @since 2.0.0
     */
    public function map(\Magento\Framework\Api\CriteriaInterface $criteria);

    /**
     * Get resource instance
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     * @since 2.0.0
     */
    public function getResource();

    /**
     * Add attribute expression (SUM, COUNT, etc)
     * Example: ('sub_total', 'SUM({{attribute}})', 'revenue')
     * Example: ('sub_total', 'SUM({{revenue}})', 'revenue')
     * For some functions like SUM use groupByAttribute.
     *
     * @param string $alias
     * @param string $expression
     * @param array|string $fields
     * @return $this
     * @since 2.0.0
     */
    public function addExpressionFieldToSelect($alias, $expression, $fields);

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
     * </pre>
     * The above would find where age equal to 42 OR name like %Mage%.
     *
     * @param string|array $field
     * @param string|int|array $condition
     * @throws \Magento\Framework\Exception\LocalizedException if some error in the input could be detected.
     * @return void
     * @since 2.0.0
     */
    public function addFieldToFilter($field, $condition = null);

    /**
     * Reset Select object state
     *
     * @return void
     * @since 2.0.0
     */
    public function reset();
}
