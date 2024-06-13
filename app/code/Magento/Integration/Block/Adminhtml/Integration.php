<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Integration\Block\Adminhtml;

/**
 * Integration block.
 *
 * @api
 * @codeCoverageIgnore
 * @since 100.0.2
 */
class Integration extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Define actions available on the integrations grid page.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_integration';
        $this->_blockGroup = 'Magento_Integration';
        $this->_headerText = __('Integrations');
        $this->_addButtonLabel = __('Add New Integration');
        parent::_construct();
    }
}
