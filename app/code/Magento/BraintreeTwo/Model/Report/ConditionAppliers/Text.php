<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Model\Report\ConditionAppliers;

use Braintree\TextNode;

/**
 * Text applier
 */
class Text implements ApplierInterface
{
    /**
     * Apply filter condition
     *
     * @param TextNode $field
     * @param $condition
     * @param $value
     * @return bool
     */
    public function apply($field, $condition, $value)
    {
        $result = false;

        switch($condition) {
            case ApplierInterface::EQ:
                $field->is($value);
                $result = true;
                break;
        }

        return $result;
    }
}
