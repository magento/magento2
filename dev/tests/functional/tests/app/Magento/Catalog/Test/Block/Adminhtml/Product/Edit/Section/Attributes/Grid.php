<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\Attributes;

use Magento\Mtf\Client\Locator;
use Magento\Ui\Test\Block\Adminhtml\DataGrid;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;

/**
 * Product attributes grid.
 */
class Grid extends DataGrid
{
    /**
     * Grid fields map
     *
     * @var array
     */
    protected $filters = [
        'label' => [
            'selector' => '[name="frontend_label"]',
        ]
    ];

    /**
     * Checking not exist attribute in search result.
     *
     * @param CatalogProductAttribute $productAttribute
     * @return bool
     */
    public function isExistAttributeInSearchResult($productAttribute)
    {
        $this->find($this->topPage, Locator::SELECTOR_XPATH)->hover();
        $this->find($this->actionToggle)->click();

        return $this->isExistValueInSearchResult($productAttribute->getFrontendLabel());
    }
}
