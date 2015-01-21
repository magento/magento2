<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\Block\Adminhtml\View;

use Magento\Backend\Test\Block\Widget\Grid;

/**
 * Class Items
 * Adminhtml shipping items on shipment view page
 */
class Items extends Grid
{
    /**
     * Secondary part of row locator template for getRow() method
     *
     * @var string
     */
    protected $rowTemplate = 'td[contains(.,normalize-space("%s"))]';
}
