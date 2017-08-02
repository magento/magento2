<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Config\Source;

/**
 * Class \Magento\Catalog\Model\Config\Source\TimeFormat
 *
 * @since 2.0.0
 */
class TimeFormat implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return [
            ['value' => '12h', 'label' => __('12h AM/PM')],
            ['value' => '24h', 'label' => __('24h')]
        ];
    }
}
