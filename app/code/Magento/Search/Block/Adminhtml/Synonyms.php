<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Search\Block\Adminhtml;

/**
 * Adminhtml synonym group content block
 */
class Synonyms extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
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
