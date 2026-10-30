<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Menu\Builder\Command;

/**
 * Command to update menu item data
 * @api
 * @since 100.0.2
 */
class Update extends \Magento\Backend\Model\Menu\Builder\AbstractCommand
{
    /**
     * Update item data
     *
     * @param array $itemParams
     * @return array
     */
    protected function _execute(array $itemParams)
    {
        foreach ($this->_data as $key => $value) {
            $itemParams[$key] = $value;
        }
        return $itemParams;
    }
}
