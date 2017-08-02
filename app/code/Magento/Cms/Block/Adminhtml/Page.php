<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Block\Adminhtml;

/**
 * Adminhtml cms pages content block
 * @since 2.0.0
 */
class Page extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Block constructor
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_page';
        $this->_blockGroup = 'Magento_Cms';
        $this->_headerText = __('Manage Pages');

        parent::_construct();

        if ($this->_isAllowedAction('Magento_Cms::save')) {
            $this->buttonList->update('add', 'label', __('Add New Page'));
        } else {
            $this->buttonList->remove('add');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     * @since 2.0.0
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
