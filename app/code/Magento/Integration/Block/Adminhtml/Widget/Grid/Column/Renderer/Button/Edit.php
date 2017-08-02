<?php
/**
 * Render HTML <button> tag with "edit" action for the integration grid.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Button;

use Magento\Framework\DataObject;
use Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Button;

/**
 * Class \Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Button\Edit
 *
 * @since 2.0.0
 */
class Edit extends Button
{
    /**
     * Return 'onclick' action for the button (redirect to the integration edit page).
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     * @since 2.0.0
     */
    protected function _getOnclickAttribute(DataObject $row)
    {
        return sprintf("window.location.href='%s'", $this->getUrl('*/*/edit', ['id' => $row->getId()]));
    }

    /**
     * Get title depending on whether element is disabled or not.
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     * @since 2.0.0
     */
    protected function _getTitleAttribute(DataObject $row)
    {
        return $this->_isConfigBasedIntegration($row) ? __('View') : __('Edit');
    }

    /**
     * Get the icon on the grid according to the integration type
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     * @since 2.0.0
     */
    public function _getClassAttribute(DataObject $row)
    {
        $class = $this->_isConfigBasedIntegration($row) ? 'info' : 'edit';

        return 'action ' . $class;
    }
}
