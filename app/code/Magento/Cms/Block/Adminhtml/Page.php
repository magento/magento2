<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Block\Adminhtml;

/**
 * Adminhtml cms pages content block
 */
class Page extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Block constructor
     *
     * @return void
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
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
