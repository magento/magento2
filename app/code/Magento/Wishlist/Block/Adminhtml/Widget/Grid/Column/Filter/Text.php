<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Wishlist\Block\Adminhtml\Widget\Grid\Column\Filter;

class Text extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Text
{
    /**
     * Override abstract method
     *
     * @return array
     */
    public function getCondition()
    {
        return ['like' => $this->getValue()];
    }
}
