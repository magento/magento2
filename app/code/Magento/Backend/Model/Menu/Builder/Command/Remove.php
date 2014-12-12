<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Model\Menu\Builder\Command;

/**
 * Command to remove menu item
 */
class Remove extends \Magento\Backend\Model\Menu\Builder\AbstractCommand
{
    /**
     * Mark item as removed
     *
     * @param array $itemParams
     * @return array
     */
    protected function _execute(array $itemParams)
    {
        $itemParams['id'] = $this->getId();
        $itemParams['removed'] = true;
        return $itemParams;
    }
}
