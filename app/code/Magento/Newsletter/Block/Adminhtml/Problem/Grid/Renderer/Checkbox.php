<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Newsletter subscribers grid checkbox item renderer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Block\Adminhtml\Problem\Grid\Renderer;

/**
 * Class \Magento\Newsletter\Block\Adminhtml\Problem\Grid\Renderer\Checkbox
 *
 */
class Checkbox extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return '<input type="checkbox" name="problem[]" value="' . $row->getId() . '" class="problemCheckbox"/>';
    }
}
