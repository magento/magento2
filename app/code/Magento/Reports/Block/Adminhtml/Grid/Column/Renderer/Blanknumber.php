<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Reports\Block\Adminhtml\Grid\Column\Renderer;

/**
 * Adminhtml grid item renderer number or blank line
 */
class Blanknumber extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Number
{
    /**
     * Get Value
     *
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
