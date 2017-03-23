<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Model\Config\Source;

class Frequency implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'always', 'label' => __('Always')],
            ['value' => 'hourly', 'label' => __('Hourly')],
            ['value' => 'daily', 'label' => __('Daily')],
            ['value' => 'weekly', 'label' => __('Weekly')],
            ['value' => 'monthly', 'label' => __('Monthly')],
            ['value' => 'yearly', 'label' => __('Yearly')],
            ['value' => 'never', 'label' => __('Never')]
        ];
    }
}
