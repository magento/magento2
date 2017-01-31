<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Item;

use Magento\Quote\Model\Quote\Item;

/**
 * Class Compare
 */
class Compare
{
    /**
     * Returns option values adopted to compare
     *
     * @param mixed $value
     * @return mixed
     */
    protected function getOptionValues($value)
    {
        if (is_string($value) && is_array(@unserialize($value))) {
            $value = @unserialize($value);
            unset($value['qty'], $value['uenc']);
            $value = array_filter($value, function ($optionValue) {
                return !empty($optionValue);
            });
        }
        return $value;
    }

    /**
     * Compare two quote items
     *
     * @param Item $target
     * @param Item $compared
     * @return bool
     */
    public function compare(Item $target, Item $compared)
    {
        if ($target->getProductId() != $compared->getProductId()) {
            return false;
        }
        $targetOptions = $this->getOptions($target);
        $comparedOptions = $this->getOptions($compared);

        if (array_diff_key($targetOptions, $comparedOptions) != array_diff_key($comparedOptions, $targetOptions)
        ) {
            return false;
        }
        foreach ($targetOptions as $name => $value) {
            if ($comparedOptions[$name] != $value) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns options adopted to compare
     *
     * @param Item $item
     * @return array
     */
    public function getOptions(Item $item)
    {
        $options = [];
        foreach ($item->getOptions() as $option) {
            $options[$option->getCode()] = $this->getOptionValues($option->getValue());
        }
        return $options;
    }
}
