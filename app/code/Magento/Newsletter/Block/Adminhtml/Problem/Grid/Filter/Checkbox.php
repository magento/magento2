<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Newsletter subscribers grid filter checkbox
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Block\Adminhtml\Problem\Grid\Filter;

/**
 * Class \Magento\Newsletter\Block\Adminhtml\Problem\Grid\Filter\Checkbox
 *
 * @since 2.0.0
 */
class Checkbox extends \Magento\Backend\Block\Widget\Grid\Column\Filter\AbstractFilter
{
    /**
     * Get the condition of grid filter checkbox
     *
     * @return array
     * @since 2.0.0
     */
    public function getCondition()
    {
        return [];
    }

    /**
     * Get html code for grid filter checkbox
     *
     * @return string
     * @since 2.0.0
     */
    public function getHtml()
    {
        return '<input type="checkbox" onclick="problemController.checkCheckboxes(this)"/>';
    }
}
