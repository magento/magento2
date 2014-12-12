<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Indexer\Block\Backend\Grid\Column\Renderer;

class Scheduled extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Render whether indexer is scheduled
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function render(\Magento\Framework\Object $row)
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
