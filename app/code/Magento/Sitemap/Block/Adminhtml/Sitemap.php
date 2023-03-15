<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

/**
 * Adminhtml catalog (google) sitemaps block
 *
 * @api
 * @since 100.0.2
 */
class Sitemap extends Container
{
    /**
     * Block constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_sitemap';
        $this->_blockGroup = 'Magento_Sitemap';
        $this->_headerText = __('XML Sitemap');
        $this->_addButtonLabel = __('Add Sitemap');
        parent::_construct();
    }
}
