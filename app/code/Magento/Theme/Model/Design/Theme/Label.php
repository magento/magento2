<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Theme;

class Label extends \Magento\Framework\View\Design\Theme\Label
{
    /**
     * Return labels collection array
     *
     * @param bool|string $label add empty values to result with specific label
     * @return array
     */
    public function getLabelsCollection($label = false)
    {
        $options = parent::getLabelsCollection();
        if ($label) {
            array_unshift($options, ['value' => 0, 'label' => $label]);
        }
        return $options;
    }
}
