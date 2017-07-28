<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Theme;

/**
 * Class \Magento\Theme\Model\Design\Theme\Label
 *
 * @since 2.1.0
 */
class Label extends \Magento\Framework\View\Design\Theme\Label
{
    /**
     * Return labels collection array
     *
     * @param bool|string $label add empty values to result with specific label
     * @return array
     * @since 2.1.0
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
