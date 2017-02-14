<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product;

use Magento\Mtf\Client\Locator;
use Magento\Ui\Test\Block\Adminhtml\DataGrid;

/**
 * Associated products grid.
 */
class AssociatedProductGrid extends DataGrid
{
    /**
     * Checkbox for selection an item in grid.
     *
     * @var string
     */
    protected $selectItem = '.data-grid-checkbox-cell-inner';

    /**
     * Filter for the search in grid.
     *
     * @var array
     */
    protected $filters = [
        'sku' => [
            'selector' => '[name="sku"]',
        ],
    ];

    /**
     * Button selector to close popup with products grid.
     *
     * @var string
     */
    protected $closeButton = '/*//button/span[contains(text(),"Done")]';

    /**
     * Close grid of added manually products.
     */
    public function closeGrid()
    {
        $this->browser->find($this->closeButton, Locator::SELECTOR_XPATH)->click();
    }
}
