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
 * @since 2.0.0
 */
class Grid extends BackendGrid
{
    /**
     * Disable javascript callback on row clicking.
     *
     * @return string
     * @since 2.0.0
     */
    public function getRowClickCallback()
    {
        return '';
    }

    /**
     * Disable javascript callback on row init.
     *
     * @return string
     * @since 2.0.0
     */
    public function getRowInitCallback()
    {
        return '';
    }
}
