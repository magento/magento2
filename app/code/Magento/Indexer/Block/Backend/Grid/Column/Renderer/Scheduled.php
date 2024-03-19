<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Block\Backend\Grid\Column\Renderer;

use Magento\Customer\Model\Customer;

/**
 * Renderer for 'Scheduled' column in indexer grid
 */
class Scheduled extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Render whether indexer is scheduled
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($this->isPreferRealtime($row->getIndexerId())) {
            $scheduleClass = 'grid-severity-major';
            $realtimeClass = 'grid-severity-notice';
        } else {
            $scheduleClass = 'grid-severity-notice';
            $realtimeClass = 'grid-severity-major';
        }

        if ($this->_getValue($row)) {
            $class = $scheduleClass;
            $text = __('Update by Schedule');
        } else {
            $class = $realtimeClass;
            $text = __('Update on Save');
        }

        return '<span class="' . $class . '"><span>' . $text . '</span></span>';
    }

    /**
     * Determine if an indexer is recommended to be in 'realtime' mode
     *
     * @param string $indexer
     * @return bool
     */
    public function isPreferRealtime(string $indexer): bool
    {
        return in_array($indexer, [
            Customer::CUSTOMER_GRID_INDEXER_ID,
        ]);
    }
}
