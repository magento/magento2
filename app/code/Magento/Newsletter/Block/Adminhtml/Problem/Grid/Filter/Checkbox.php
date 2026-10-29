<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

/**
 * Newsletter subscribers grid filter checkbox
 */
namespace Magento\Newsletter\Block\Adminhtml\Problem\Grid\Filter;

class Checkbox extends \Magento\Backend\Block\Widget\Grid\Column\Filter\AbstractFilter
{
    /**
     * Get the condition of grid filter checkbox
     *
     * @return array
     */
    public function getCondition()
    {
        return [];
    }

    /**
     * Get html code for grid filter checkbox
     *
     * @return string
     */
    public function getHtml()
    {
        return '<input type="checkbox" onclick="problemController.checkCheckboxes(this)"/>';
    }
}
