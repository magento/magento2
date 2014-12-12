<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
