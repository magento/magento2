<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Rule\Model\Condition;

/**
 * @api
 * @since 100.0.2
 */
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
