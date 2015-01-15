<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Block\Adminhtml\Grid\Renderer;

/**
 * Sitemap grid action column renderer
 */
class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{
    /**
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function render(\Magento\Framework\Object $row)
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
