<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Test\Block\Adminhtml;

use Magento\Backend\Test\Block\Widget\Grid;

/**
 * Adminhtml Data Grid with advanced functionality for managing entities.
 */
class DataGrid extends Grid
{
    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Locator value for link in action column.
     *
     * @var string
     */
    protected $editLink = '[data-action="grid-row-edit"]';

    /**
     * Container for applied filters.
     *
     * @var string
     */
    protected $appliedFiltersList = '[data-role="filter-list"]';

    /**
     * Filter button
     *
     * @var string
     */
    protected $filterButton = '[data-action="grid-filter-expand"]';

    /**
     * Clear all applied Filters
     */
    public function resetFilter()
    {
        $chipsHolder = $this->_rootElement->find($this->appliedFiltersList);
        if ($chipsHolder->isVisible()) {
            parent::resetFilter();
        }
    }

    /**
     * Open Filter Block
     *
     * @return void
     */
    protected function openFilterBlock()
    {
        $this->getTemplateBlock()->waitForElementNotVisible($this->loader);

        $toggleFilterButton = $this->_rootElement->find($this->filterButton);
        $searchButton = $this->_rootElement->find($this->searchButton);
        if ($toggleFilterButton->isVisible() && !$searchButton->isVisible()) {
            $toggleFilterButton->click();
            $browser = $this->_rootElement;
            $browser->waitUntil(
                function () use ($searchButton) {
                    return $searchButton->isVisible() ? true : null;
                }
            );
        }
    }

    /**
     * Search item via grid filter
     *
     * @param array $filter
     */
    public function search(array $filter)
    {
        $this->openFilterBlock();
        parent::search($filter);
    }
}
