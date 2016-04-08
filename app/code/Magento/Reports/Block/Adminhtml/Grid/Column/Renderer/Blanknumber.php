<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Adminhtml\Grid\Column\Renderer;

/**
 * Adminhtml grid item renderer number or blank line
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Blanknumber extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Number
{
    /**
     * @param \Magento\Framework\DataObject $row
     *
     * @return string
     */
    protected function _getValue(\Magento\Framework\DataObject $row)
    {
        $data = parent::_getValue($row);
        if ($data !== null) {
            $value = $data * 1;
            return $value ? $value : ''; // fixed for showing blank cell in grid
            /**
             * @todo may be bug in i.e. needs to be fixed
             */
        }
        return $this->getColumn()->getDefault();
    }
}
