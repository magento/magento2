<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Block\Adminhtml;

/**
 * @api
 * @since 100.0.2
 */
class Term extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'term';
        $this->_headerText = __('Search');
        $this->_addButtonLabel = __('Add New Search Term');
        parent::_construct();
    }
}
