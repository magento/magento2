<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Rule\Model\Condition;

interface ConditionInterface
{
    /**
     * Get tables to join
     *
     * @return array
     */
    public function getTablesToJoin();

    /**
     * Get field by attribute
     *
     * @return string
     */
    public function getMappedSqlField();

    /**
     * Get argument value to bind
     *
     * @return mixed
     */
    public function getBindArgumentValue();
}
