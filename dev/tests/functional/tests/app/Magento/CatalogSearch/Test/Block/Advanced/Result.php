<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Block\Advanced;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Class Result
 * Block for advanced search results
 */
class Result extends Block
{
    /**
     * CSS selector for block 'Search summary'
     *
     * @var string
     */
    protected $searchSummary = '.search.summary';

    /**
     * XPath selector for block 'Search summary' item
     *
     * @var string
     */
    protected $searchSummaryItem = './/*[@class="item"][%d]';

    /**
     * XPath selector for block 'Search summary' items
     *
     * @var string
     */
    protected $searchSummaryItems = './/*[@class="items"][%d]';

    /**
     * XPath selector for block 'Messages'
     *
     * @var string
     */
    protected $messagesSelector = './/*[contains(@class,"message") and contains(.,"%s")]';

    /**
     * Checking the presence of the messages on the page
     *
     * @param string $text
     * @return bool
     */
    public function isVisibleMessages($text)
    {
        return $this->_rootElement->find(sprintf($this->messagesSelector, $text), Locator::SELECTOR_XPATH)
            ->isVisible();
    }

    /**
     * Getting search data
     *
     * @return array
     */
    public function getSearchSummaryItems()
    {
        $result = [];
        $index = 1;

        $element = $this->_rootElement->find($this->searchSummary);
        while ($element->find(sprintf($this->searchSummaryItems, $index), Locator::SELECTOR_XPATH)->isVisible()) {
            $parentElement = $element->find(sprintf($this->searchSummaryItems, $index), Locator::SELECTOR_XPATH);
            $childIndex = 1;
            while ($parentElement->find(
                sprintf($this->searchSummaryItem, $childIndex),
                Locator::SELECTOR_XPATH
            )->isVisible()) {
                $result[] = $parentElement->find(
                    sprintf($this->searchSummaryItem, $childIndex),
                    Locator::SELECTOR_XPATH
                )->getText();
                ++$childIndex;
            }
            ++$index;
        }

        // Prepare data
        foreach ($result as $key => $dataRow) {
            $explodeData = explode(':', $dataRow);
            $explodeData[1] = trim($explodeData[1]);
            $explodeData[0] = str_replace(' ', '_', strtolower($explodeData[0]));
            $explodeData[0] = str_replace('product_', '', $explodeData[0]);
            if ($explodeData[0] === 'price') {
                $matches = [];
                if (preg_match('#^(\d+)[^\d]+(\d+)$#umis', $explodeData[1], $matches)) { // range
                    $result[$explodeData[0]][] = $matches[1];
                    $result[$explodeData[0]][] = $matches[2];
                } elseif (preg_match('#^[^\d]+(\d+)$#umis', $explodeData[1], $matches)) { // up to
                    $result[$explodeData[0]][] = $matches[1];
                } elseif (preg_match('#^(\d+)[^\d]+$#umis', $explodeData[1], $matches)) { // greater
                    $result[$explodeData[0]][] = $matches[1];
                }
            } else {
                $result[$explodeData[0]] = explode(',', $explodeData[1]);
            }
            $result[$explodeData[0]] = array_map('trim', $result[$explodeData[0]]);
            unset($result[$key]);
        }

        return $result;
    }
}
