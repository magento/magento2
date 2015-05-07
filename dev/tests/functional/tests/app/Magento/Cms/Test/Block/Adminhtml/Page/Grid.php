<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Block\Adminhtml\Page;

use Magento\Backend\Test\Block\Widget\Grid as ParentGrid;
use Magento\Mtf\Client\Locator;

/**
 * Backend Cms Page grid.
 */
class Grid extends ParentGrid
{
    /**
     * Locator value for 'Search' button.
     *
     * @var string
     */
    protected $searchButton = '[data-action="grid-filter-apply"]';

    /**
     * Locator value for 'Reset' button.
     *
     * @var string
     */
    protected $resetButton = '[data-action="grid-filter-reset"]';

    /**
     * Locator value for link in action column.
     *
     * @var string
     */
    protected $editLink = '[data-action="grid-row-edit"]';

    /**
     * 'Preview' cms page link.
     *
     * @var string
     */
    protected $previewCmsPage = "//a[contains(text(),'Preview')]";

    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'title' => [
            'selector' => '[name="params[filters][title]"]',
        ],
    ];

    /**
     * Container for applied filters.
     *
     * @var string
     */
    protected $appliedFiltersList = '[data-role="filter-list"]';

    /**
     * Search item and open it on front.
     *
     * @param array $filter
     * @throws \Exception
     * @return void
     */
    public function searchAndPreview(array $filter)
    {
        $this->search($filter);
        $rowItem = $this->_rootElement->find($this->rowItem);
        if ($rowItem->isVisible()) {
            $rowItem->find($this->previewCmsPage, Locator::SELECTOR_XPATH)->click();
            $this->waitForElement();
        } else {
            throw new \Exception('Searched item was not found.');
        }
    }

    /**
     * Clear all applied Filters
     */
    public function resetFilter()
    {
        $chipsHolder = $this->_rootElement->find($this->appliedFiltersList);
        if ($chipsHolder->isVisible()) {
            $this->_rootElement->find($this->resetButton)->click();
            $this->waitLoader();
        }
    }
}
