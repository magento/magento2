<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Usps\Model\Source;

/**
 * Freemethod source
 */
class Freemethod extends Method
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();

        array_unshift($options, ['value' => '', 'label' => __('None')]);
        return $options;
    }
}
