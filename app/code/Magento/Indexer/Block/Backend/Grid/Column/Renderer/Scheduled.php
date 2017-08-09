<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Block\Backend\Grid\Column\Renderer;

/**
 * Class \Magento\Indexer\Block\Backend\Grid\Column\Renderer\Scheduled
 *
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
        if ($this->_getValue($row)) {
            $class = 'grid-severity-notice';
            $text = __('Update by Schedule');
        } else {
            $class = 'grid-severity-major';
            $text = __('Update on Save');
        }
        return '<span class="' . $class . '"><span>' . $text . '</span></span>';
    }
}
