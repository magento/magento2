<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Log\Block\Adminhtml\Online\Grid\Renderer;

/**
 * Adminhtml Online Customer last URL renderer
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Url extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\Object $row
     * @return  string
     */
    public function render(\Magento\Framework\Object $row)
    {
        return htmlspecialchars($row->getData($this->getColumn()->getIndex()));
    }
}
