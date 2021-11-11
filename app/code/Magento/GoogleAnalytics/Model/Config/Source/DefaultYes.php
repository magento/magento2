<?php
/**
 * Google AdWords conversation value type source
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleAnalytics\Model\Config\Source;

class DefaultYes implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Get default yes option
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => \Magento\GoogleAnalytics\Helper\Data::DEFAULT_YES,
                'label' => __('Yes'),
            ]
        ];
    }
}
