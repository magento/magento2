<?php
/**
 * Integration grid.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Integration\Block\Adminhtml\Integration;

use Magento\Backend\Block\Widget\Grid as BackendGrid;

class Grid extends BackendGrid
{
    /**
     * Disable javascript callback on row clicking.
     *
     * @return string
     */
    public function getRowClickCallback()
    {
        return '';
    }

    /**
     * Disable javascript callback on row init.
     *
     * @return string
     */
    public function getRowInitCallback()
    {
        return '';
    }
}
