<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Adminhtml grid item renderer number
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Tax\Block\Adminhtml\Rate\Grid\Renderer;

class Data extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @param \Magento\Framework\Object $row
     * @return int|string
     */
    protected function _getValue(\Magento\Framework\Object $row)
    {
        $data = parent::_getValue($row);
        if (intval($data) == $data) {
            return (string)number_format($data, 2);
        }
        if (!is_null($data)) {
            return $data * 1;
        }
        return $this->getColumn()->getDefault();
    }
}
