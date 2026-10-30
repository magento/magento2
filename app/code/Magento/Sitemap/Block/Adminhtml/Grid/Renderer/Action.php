<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sitemap\Block\Adminhtml\Grid\Renderer;

/**
 * Sitemap grid action column renderer
 */
class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{
    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $this->getColumn()->setActions(
            [
                [
                    'url' => $this->getUrl('adminhtml/sitemap/generate', ['sitemap_id' => $row->getSitemapId()]),
                    'caption' => __('Generate'),
                ],
            ]
        );
        return parent::render($row);
    }
}
