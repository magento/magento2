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

class Delete extends Button
{
    /**
     * Return 'onclick' action for the button (redirect to the integration edit page).
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    protected function _getOnclickAttribute(Object $row)
    {
        return sprintf(
            "this.setAttribute('data-url', '%s')",
            $this->getUrl('*/*/delete', ['id' => $row->getId()])
        );
    }

    /**
     * Get title depending on whether element is disabled or not.
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    protected function _getTitleAttribute(Object $row)
    {
        return $this->_isDisabled($row) ? __('Uninstall the extension to remove this integration') : __('Remove');
    }

    /**
     * Determine whether current integration came from config file, thus can not be removed
     *
     * @param \Magento\Framework\Object $row
     * @return bool
     */
    protected function _isDisabled(Object $row)
    {
        return $this->_isConfigBasedIntegration($row);
    }
}
