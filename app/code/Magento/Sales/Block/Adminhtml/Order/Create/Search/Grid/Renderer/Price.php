<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer;

/**
 * Adminhtml sales create order product search grid price column renderer
 */
class Price extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Price
{
    /**
     * Render minimal price for downloadable products
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($row->getTypeId() == 'downloadable') {
            $row->setPrice($row->getPrice());
        }
        return parent::render($row);
    }
}
