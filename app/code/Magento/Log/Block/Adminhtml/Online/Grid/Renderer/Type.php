<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Log\Block\Adminhtml\Online\Grid\Renderer;

/**
 * Adminhtml customers online grid renderer for customer type.
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Type extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function render(\Magento\Framework\Object $row)
    {
        return $row->getCustomerId() > 0 ? __('Customer') : __('Visitor');
    }
}
