<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Block;

class GridContainer extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Part for generating appropriate grid block name
     *
     * @var string
     */
    protected $_controller = 'url_rewrite';

    /**
     * @var \Magento\UrlRewrite\Block\Selector
     */
    protected $_urlrewriteSelector;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param Selector $urlrewriteSelector
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\UrlRewrite\Block\Selector $urlrewriteSelector,
        array $data = []
    ) {
        $this->_urlrewriteSelector = $urlrewriteSelector;
        parent::__construct($context, $data);
    }

    /**
     * Set custom labels and headers
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_headerText = __('URL Rewrite Management');
        $this->_addButtonLabel = __('Add URL Rewrite');
        parent::_construct();
    }

    /**
     * Customize grid row URLs
     *
     * @return string
     */
    public function getCreateUrl()
    {
        $url = $this->getUrl('adminhtml/*/edit');

        $selectorBlock = $this->getSelectorBlock();
        if ($selectorBlock === null) {
            $selectorBlock = $this->_urlrewriteSelector;
        }

        if ($selectorBlock) {
            $modes = array_keys($selectorBlock->getModes());
            $url .= reset($modes);
        }

        return $url;
    }
}
