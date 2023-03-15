<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Block\Adminhtml\Grid\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\Action as ColumnRendererAction;
use Magento\Framework\DataObject;

/**
 * Sitemap grid action column renderer
 */
class Action extends ColumnRendererAction
{
    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
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
