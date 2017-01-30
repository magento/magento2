<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\LayeredNavigation\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Catalog layered navigation view block.
 */
class Navigation extends Block
{
    /**
     * Locator for loaded "narrow-by-list" block.
     *
     * @var string
     */
    protected $loadedNarrowByList = '#narrow-by-list[role="tablist"]';

    /**
     * Locator value for "Clear All" link.
     *
     * @var string
     */
    protected $clearAll = '.action.clear';

    /**
     * Locator value for correspondent Attribute filter option.
     *
     * @var string
     */
    protected $optionTitle = './/div[@class="filter-options-title" and contains(text(),"%s")]';

    /**
     * Locator value for correspondent "Filter" link.
     *
     * @var string
     */
    protected $filterLink = './/div[@class="filter-options-title" and contains(text(),"%s")]/following-sibling::div//a';

    /**
     * Locator value for "Expand Filter" button.
     *
     * @var string
     */
    protected $expandFilterButton = '[data]';

    /**
     * Remove all applied filters.
     *
     * @return void
     */
    public function clearAll()
    {
        $this->_rootElement->find($this->clearAll)->click();
    }

    /**
     * Get all available filters.
     *
     * @return array
     */
    public function getFilters()
    {
        $this->waitForElementVisible($this->loadedNarrowByList);

        $options = $this->_rootElement->getElements(sprintf($this->optionTitle, ''), Locator::SELECTOR_XPATH);
        $data = [];
        foreach ($options as $option) {
            $data[] = strtoupper($option->getText());
        }

        return $data;
    }

    /**
     * Apply Layerd Navigation filter.
     *
     * @param string $filter
     * @param string $linkPattern
     * @return void
     * @throws \Exception
     */
    public function applyFilter($filter, $linkPattern)
    {
        $expandFilterButton = sprintf($this->optionTitle, $filter);
        $links = sprintf($this->filterLink, $filter);

        $this->waitForElementVisible($this->loadedNarrowByList);
        if (!$this->_rootElement->find($links, Locator::SELECTOR_XPATH)->isVisible()) {
            $this->_rootElement->find($expandFilterButton, Locator::SELECTOR_XPATH)->click();
        }

        $links = $this->_rootElement->getElements($links, Locator::SELECTOR_XPATH);
        foreach ($links as $link) {
            if (preg_match($linkPattern, trim($link->getText()))) {
                $link->click();
                return;
            }
        }
        throw new \Exception("Can't find {$filter} filter link by pattern: {$linkPattern}");
    }
}
