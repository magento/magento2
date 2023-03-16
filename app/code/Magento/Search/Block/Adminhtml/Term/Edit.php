<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Block\Adminhtml\Term;

use Magento\Backend\Block\Widget\Context as WidgetContext;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;

/**
 * Admin term edit block
 *
 * @api
 * @since 100.0.2
 */
class Edit extends Container
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @param WidgetContext $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        WidgetContext $context,
        Registry $registry,
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
     * @return Phrase
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
