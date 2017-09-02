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
        $availableOptions = array(
            'all',
            'error',
            'missed',
            'pending',
            'running',
            'success'
        );
        
        $options[] = array(
            'label' => '',
            'value' => ''
        );
        foreach ($availableOptions as $value) {
            $options[] = array(
                'label' => $value,
                'value' => $value,
            );
        }

        return $options;
    }
}
