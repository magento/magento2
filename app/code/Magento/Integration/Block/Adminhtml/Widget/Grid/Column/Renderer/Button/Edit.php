<?php
/**
 * Render HTML <button> tag with "edit" action for the integration grid.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Button;

use Magento\Framework\Object;
use Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Button;

class Edit extends Button
{
    /**
     * Return 'onclick' action for the button (redirect to the integration edit page).
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    protected function _getOnclickAttribute(Object $row)
    {
        return sprintf("window.location.href='%s'", $this->getUrl('*/*/edit', ['id' => $row->getId()]));
    }

    /**
     * Get title depending on whether element is disabled or not.
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    protected function _getTitleAttribute(Object $row)
    {
        return $this->_isConfigBasedIntegration($row) ? __('View') : __('Edit');
    }

    /**
     * Get the icon on the grid according to the integration type
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function _getClassAttribute(Object $row)
    {
        $class = $this->_isConfigBasedIntegration($row) ? 'info' : 'edit';

        return 'action ' . $class;
    }
}
