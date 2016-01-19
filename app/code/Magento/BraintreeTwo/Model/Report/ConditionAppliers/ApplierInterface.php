<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Model\Report\ConditionAppliers;

/**
 * Braintree filter condition applier interface
 */
interface ApplierInterface
{
    const EQ = 'eq';
    const QTEQ = 'gteq';
    const LTEQ = 'lteq';
    const IN = 'in';
    const IS = 'is';

    /**
     * Apply filter condition
     *
     * @param $field
     * @param $condition
     * @param $value
     * @return bool
     */
    public function apply($field, $condition, $value);
}
