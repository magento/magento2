<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * 'Clear All' link.
     *
     * @var string
     */
    protected $clearAll = '.action.clear';

    /**
     * Attribute option title selector.
     *
     * @var string
     */
    protected $optionTitle = '.filter-options-title';

    /**
     * Filter link locator.
     *
     * @var string
     */
    protected $filterLink = './/dt[contains(text(),"%s")]/following-sibling::dd//a';

    /**
     * Click on 'Clear All' link.
     *
     * @return void
     */
    public function clearAll()
    {
        $this->_rootElement->find($this->clearAll, locator::SELECTOR_CSS)->click();
    }

    /**
     * Get array of available filters.
     *
     * @return array
     */
    public function getFilters()
    {
        $options = $this->_rootElement->getElements($this->optionTitle);
        $data = [];
        foreach ($options as $option) {
            $data[] = $option->getText();
        }
        return $data;
    }

    /**
     * Click filter link.
     *
     * @param string $filter
     * @param string $linkPattern
     * @return void
     * @throws \Exception
     */
    public function clickFilterLink($filter, $linkPattern)
    {
        $links = $this->_rootElement->getElements(sprintf($this->filterLink, $filter), Locator::SELECTOR_XPATH);

        foreach ($links as $link) {
            if (preg_match($linkPattern, trim($link->getText()))) {
                $link->click();
                return;
            }
        }
        throw new \Exception("Can't find {$filter} filter link by pattern: {$linkPattern}");
    }
}
