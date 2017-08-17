<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer;

/**
 * Adminhtml sales create order product search grid product name column renderer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Product extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    /**
     * Render product name to add Configure link
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $rendered = parent::render($row);
        $isConfigurable = $row->canConfigure();
        $style = $isConfigurable ? '' : 'disabled';
        $prodAttributes = $isConfigurable ? sprintf(
            'list_type = "product_to_add" product_id = %s',
            $row->getId()
        ) : 'disabled="disabled"';
        return sprintf(
            '<a href="javascript:void(0)" class="action-configure %s" %s>%s</a>',
            $style,
            $prodAttributes,
            __('Configure')
        ) . $rendered;
    }
}
