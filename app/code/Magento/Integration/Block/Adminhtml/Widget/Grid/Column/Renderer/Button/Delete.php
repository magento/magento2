<?php
/**
 * Render HTML <button> tag with "edit" action for the integration grid.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
