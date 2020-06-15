<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Report\ConditionAppliers;

/**
 * Braintree filter condition applier interface
 *
 * @deprecated Starting from Magento 2.3.6 Braintree payment method core integration is deprecated
 * in favor of official payment integration available on the marketplace
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
