<?php
/**
 * Integration grid.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Block\Adminhtml\Integration;

use Magento\Backend\Block\Widget\Grid as BackendGrid;

/**
 * @api
 * @codeCoverageIgnore
 * @since 100.0.2
 */
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
