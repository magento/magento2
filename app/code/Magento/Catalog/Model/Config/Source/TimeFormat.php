<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\Config\Source;

class TimeFormat implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => '12h', 'label' => __('12h AM/PM')],
            ['value' => '24h', 'label' => __('24h')]
        ];
    }
}
