<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Report\ConditionAppliers;

/**
 * Braintree filter condition applier interface
 */
interface ApplierInterface
{
    const EQ = 'eq';
    const QTEQ = 'gteq';
    const LTEQ = 'lteq';
    const IN = 'in';
    const LIKE = 'like';

    /**
     * Apply filter condition
     *
     * @param object $field
     * @param string $condition
     * @param mixed $value
     * @return bool
     */
    public function apply($field, $condition, $value);
}
