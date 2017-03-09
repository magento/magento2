<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Adminhtml\Grid\Column\Renderer;

/**
 * Adminhtml Report Products Reviews renderer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Product extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Renders grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $id = $row->getId();

        return sprintf(
            '<a href="%s">%s</a>',
            $this->getUrl('review/product/', ['productId' => $id]),
            __('Show Reviews')
        );
    }
}
