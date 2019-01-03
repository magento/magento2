<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Block\Adminhtml\Product\Grouped\AssociatedProducts\Search;

use Magento\Ui\Test\Block\Adminhtml\DataGrid;

/**
 * 'Add Products to Grouped product list' grid.
 */
class Grid extends DataGrid
{
    /**
     * 'Add Selected Products' button
     *
     * @var string
     */
    protected $addProducts = '.action-primary[data-role="action"]';

    /**
     * Grid selector.
     *
     * @var string
     */
    private $gridSelector = '[data-role="grid-wrapper"]';

    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [
        'name' => [
            'selector' => '[name="name"]',
        ],
    ];

    /**
     * Press 'Add Selected Products' button
     *
     * @return void
     */
    public function addProducts()
    {
        $this->_rootElement->find($this->addProducts)->click();
    }

    /**
     * @inheritdoc
     */
    public function searchAndSelect(array $filter)
    {
        $this->waitGridVisible();
        $this->waitLoader();
        parent::searchAndSelect($filter);
    }

    /**
     * @inheritdoc
     */
    protected function waitLoader()
    {
        parent::waitLoader();
        $this->waitGridLoaderInvisible();
    }

    /**
     * Wait for grid to appear.
     *
     * @return void
     */
    private function waitGridVisible()
    {
        $browser = $this->_rootElement;
        $selector = $this->gridSelector;

        return $browser->waitUntil(
            function () use ($browser, $selector) {
                $element = $browser->find($selector);
                return $element->isVisible() ? true : null;
            }
        );
    }

    /**
     * Wait for grid spinner disappear.
     *
     * @return void
     */
    private function waitGridLoaderInvisible()
    {
        $browser = $this->_rootElement;
        $selector = $this->loader;

        return $browser->waitUntil(
            function () use ($browser, $selector) {
                $element = $browser->find($selector);
                return $element->isVisible() === false ? true : null;
            }
        );
    }
}
