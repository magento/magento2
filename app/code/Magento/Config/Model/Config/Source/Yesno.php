<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Model\Config\Source;

/**
 * @api
 * @since 100.0.2
 */
class Yesno implements \Magento\Framework\Option\ArrayInterface
{
    private $optionArray = null;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->optionArray === null) {
            $array = $this->toArray();
            $array = array_reverse($array, true);

            $optionArray = [];

            foreach ($array as $value => $label) {
                $optionArray[] = $this->toOption($value, $label);
            }

            $this->optionArray = $optionArray;
        }

        return $this->optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [0 => __('No'), 1 => __('Yes')];
    }

    /**
     * @param $value
     * @param $label
     * @return array
     */
    private function toOption($value, $label)
    {
        return [
            'value' => $value,
            'label' => $label
        ];
    }
}
