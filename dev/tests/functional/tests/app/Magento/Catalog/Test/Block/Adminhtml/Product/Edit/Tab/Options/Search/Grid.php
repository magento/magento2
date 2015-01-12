<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Options\Search;

use Magento\Backend\Test\Block\Widget\Grid as GridInterface;

/**
 * Class Grid
 * 'Import custom options to product' grid
 */
class Grid extends GridInterface
{
    /**
     * Selector for 'Import' button
     *
     * @var string
     */
    protected $importProducts = '#import-custom-options-apply-button';

    /**
     * An element locator which allows to select entities in grid
     *
     * @var string
     */
    protected $selectItem = 'tbody tr .col-id';

    /**
     * Filters param for grid
     *
     * @var array
     */
    protected $filters = [
        'sku' => [
            'selector' => 'input[name=sku]',
        ],
    ];

    /**
     * Press 'Import' button
     *
     * @return void
     */
    public function addProducts()
    {
        $this->_rootElement->find($this->importProducts)->click();
        $this->getTemplateBlock()->waitForElementNotVisible($this->importProducts);
    }
}
