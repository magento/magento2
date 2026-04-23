<?php
/**
 * Copyright 2012 Adobe
 * All Rights Reserved.
 */
namespace Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid\Column\Renderer;

/**
 * Coupon codes grid "Used" column renderer
 */
class Used extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    /**
     * Render
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $value = (int)$row->getData($this->getColumn()->getIndex());
        return empty($value) ? __('No') : __('Yes');
    }
}
