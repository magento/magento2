<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cron\Model\Backend\Source;

class Status implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Get options
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = [
            \Magento\Cron\Model\Schedule::STATUS_ERROR,
            \Magento\Cron\Model\Schedule::STATUS_MISSED,
            \Magento\Cron\Model\Schedule::STATUS_PENDING,
            \Magento\Cron\Model\Schedule::STATUS_RUNNING,
            \Magento\Cron\Model\Schedule::STATUS_SUCCESS
        ];

        $options[] = [
            'label' => 'all',
            'value' => ''
        ];

        foreach ($availableOptions as $value) {
            $options[] = [
                'label' => $value,
                'value' => $value,
            ];
        }

        return $options;
    }
}
