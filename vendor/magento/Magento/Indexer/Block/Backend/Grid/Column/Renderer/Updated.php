<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Indexer\Block\Backend\Grid\Column\Renderer;

class Updated extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Datetime
{
    /**
     * Render indexer updated time
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function render(\Magento\Framework\Object $row)
    {
        $value = parent::render($row);
        if (!$value) {
            return __('Never');
        }
        return $value;
    }
}
