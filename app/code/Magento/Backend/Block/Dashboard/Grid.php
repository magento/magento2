<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Dashboard;

/**
 * Adminhtml dashboard grid
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @api
 * @since 2.0.0
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'dashboard/grid.phtml';

    /**
     * Setting default for every grid on dashboard
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setDefaultLimit(5);
    }
}
