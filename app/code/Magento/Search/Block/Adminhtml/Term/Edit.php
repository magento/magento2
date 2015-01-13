<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Block\Adminhtml\Term;

/**
 * Admin term edit block
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Magento_Search';
        $this->_controller = 'adminhtml_term';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Search'));
        $this->buttonList->update('delete', 'label', __('Delete Search'));
    }

    /**
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->coreRegistry->registry('current_catalog_search')->getId()) {
            $queryText = $this->escapeHtml($this->coreRegistry->registry('current_catalog_search')->getQueryText());
            return __("Edit Search '%1'", $queryText);
        } else {
            return __('New Search');
        }
    }
}
