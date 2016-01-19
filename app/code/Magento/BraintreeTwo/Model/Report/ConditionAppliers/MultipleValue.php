<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Model\Report\ConditionAppliers;

use Braintree\MultipleValueNode;

/**
 * MultipleValue applier
 */
class MultipleValue implements ApplierInterface
{
    /**
     * Apply filter condition
     *
     * @param MultipleValueNode $field
     * @param $condition
     * @param $value
     * @return bool
     */
    public function apply($field, $condition, $value)
    {
        $result = false;

        switch($condition) {
            case ApplierInterface::IN:
                $field->in($value);
                $result = true;
                break;
            case ApplierInterface::EQ:
                $field->is($value);
                $result = true;
                break;
        }

        return $result;
    }
}
