<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Indexer\Block\Backend\Grid\Column\Renderer;

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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isPreferRealtime(string $indexer): bool
    {
        return false;
    }
}
