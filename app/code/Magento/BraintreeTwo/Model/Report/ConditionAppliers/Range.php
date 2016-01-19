<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Model\Report\ConditionAppliers;

use Braintree\RangeNode;

/**
 * Range applier
 */
class Range implements ApplierInterface
{
    /**
     * @param RangeNode $field
     * @param $condition
     * @param $value
     * @return bool
     */
    public function apply($field, $condition, $value)
    {
        $result = false;

        switch($condition) {
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
