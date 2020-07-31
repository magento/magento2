<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Report\ConditionAppliers;

use Braintree\RangeNode;

/**
 * Range applier
 *
 * @deprecated Starting from Magento 2.3.6 Braintree payment method core integration is deprecated
 * in favor of official payment integration available on the marketplace
 */
class Range implements ApplierInterface
{
    /**
     * Apply filter condition
     *
     * @param RangeNode $field
     * @param string $condition
     * @param mixed $value
     * @return bool
     */
    public function apply($field, $condition, $value)
    {
        $result = false;

        switch ($condition) {
            case ApplierInterface::QTEQ:
                $field->greaterThanOrEqualTo($value);
                $result = true;
                break;
            case ApplierInterface::LTEQ:
                $field->lessThanOrEqualTo($value);
                $result = true;
                break;
        }

        return $result;
    }
}
