<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
