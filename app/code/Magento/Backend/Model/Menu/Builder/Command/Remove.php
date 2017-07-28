<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Menu\Builder\Command;

/**
 * Command to remove menu item
 * @api
 * @since 2.0.0
 */
class Remove extends \Magento\Backend\Model\Menu\Builder\AbstractCommand
{
    /**
     * Mark item as removed
     *
     * @param array $itemParams
     * @return array
     * @since 2.0.0
     */
    protected function _execute(array $itemParams)
    {
        $itemParams['id'] = $this->getId();
        $itemParams['removed'] = true;
        return $itemParams;
    }
}
