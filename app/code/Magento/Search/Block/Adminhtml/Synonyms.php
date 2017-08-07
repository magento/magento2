<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Block\Adminhtml;

/**
 * Adminhtml synonym group content block
 * @since 2.1.0
 */
class Synonyms extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     * @since 2.1.0
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_Search';
        $this->_controller = 'adminhtml_synonyms';
        $this->_headerText = __('Search Synonyms');
        $this->_addButtonLabel = __('New Synonym Group');
        parent::_construct();
    }
}
