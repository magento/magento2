<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\System\Store\Grid\Render;

/**
 * Store render website
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Website extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return '<a title="' . __(
            'Edit Web Site'
        ) . '"
            href="' .
        $this->getUrl('adminhtml/*/editWebsite', ['website_id' => $row->getWebsiteId()]) .
        '">' .
        $this->escapeHtml($row->getData($this->getColumn()->getIndex())) .
        '</a><br />' .
        '(' . __('Code') . ': ' . $row->getCode() . ')';
    }
}
