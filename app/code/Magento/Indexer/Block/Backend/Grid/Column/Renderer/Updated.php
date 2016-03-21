<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Block\Backend\Grid\Column\Renderer;

class Updated extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Datetime
{
    /**
     * Render indexer updated time
     *
     * @param \Magento\Framework\DataObject $row
     * @return \Magento\Framework\Phrase|string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $value = parent::render($row);
        if (!$value) {
            return __('Never');
        }
        return $value;
    }
}
