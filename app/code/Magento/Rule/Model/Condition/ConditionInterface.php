<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rule\Model\Condition;

/**
 * @api
 * @since 2.0.0
 */
interface ConditionInterface
{
    /**
     * Get tables to join
     *
     * @return array
     * @since 2.0.0
     */
    public function getTablesToJoin();

    /**
     * Get field by attribute
     *
     * @return string
     * @since 2.0.0
     */
    public function getMappedSqlField();

    /**
     * Get argument value to bind
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getBindArgumentValue();
}
