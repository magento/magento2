<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Block\Adminhtml\Search;

/**
 * Search queries relations grid container
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Edit extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Enable grid container
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_AdvancedSearch';
        $this->_controller = 'adminhtml_search';
        $this->_headerText = __('Related Search Terms');
        $this->_addButtonLabel = __('Add New Search Term');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
