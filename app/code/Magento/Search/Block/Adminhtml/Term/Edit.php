<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Block\Adminhtml\Term;

/**
 * Admin term edit block
 *
 * @api
 * @since 2.0.0
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $coreRegistry;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
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
