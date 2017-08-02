<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser;

/**
 * Widget Instance layouts chooser
 *
 * @method getArea()
 * @method getTheme()
 * @since 2.0.0
 */
class Layout extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @var \Magento\Framework\View\Layout\PageType\Config
     * @since 2.0.0
     */
    protected $_config;

    /**
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Framework\View\Layout\PageType\Config $config
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\View\Layout\PageType\Config $config,
        array $data = []
    ) {
        $this->_config = $config;
        parent::__construct($context, $data);
    }

    /**
     * Add necessary options
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     * @since 2.0.0
     */
    protected function _beforeToHtml()
    {
        if (!$this->getOptions()) {
            $this->addOption('', __('-- Please Select --'));
            $pageTypes = $this->_config->getPageTypes();
            $this->_addPageTypeOptions($pageTypes);
        }
        return parent::_beforeToHtml();
    }

    /**
     * Add page types information to the options
     *
     * @param array $pageTypes
     * @return void
     * @since 2.0.0
     */
    protected function _addPageTypeOptions(array $pageTypes)
    {
        $label = [];
        // Sort list of page types by label
        foreach ($pageTypes as $key => $row) {
            $label[$key] = $row['label'];
        }
        array_multisort($label, SORT_STRING, $pageTypes);

        foreach ($pageTypes as $pageTypeName => $pageTypeInfo) {
            $params = [];
            $this->addOption($pageTypeName, $pageTypeInfo['label'], $params);
        }
    }
}
