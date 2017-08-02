<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Usps\Model\Source;

/**
 * Freemethod source
 * @since 2.0.0
 */
class Freemethod extends Method
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();

        array_unshift($options, ['value' => '', 'label' => __('None')]);
        return $options;
    }
}
