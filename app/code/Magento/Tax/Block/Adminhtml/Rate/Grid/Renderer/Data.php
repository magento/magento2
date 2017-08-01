<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml grid item renderer number
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Tax\Block\Adminhtml\Rate\Grid\Renderer;

/**
 * Class \Magento\Tax\Block\Adminhtml\Rate\Grid\Renderer\Data
 *
 * @since 2.0.0
 */
class Data extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @param \Magento\Framework\DataObject $row
     * @return int|string
     * @since 2.0.0
     */
    protected function _getValue(\Magento\Framework\DataObject $row)
    {
        $data = parent::_getValue($row);
        if (intval($data) == $data) {
            return (string)number_format($data, 2);
        }
        if ($data !== null) {
            return $data * 1;
        }
        return $this->getColumn()->getDefault();
    }
}
