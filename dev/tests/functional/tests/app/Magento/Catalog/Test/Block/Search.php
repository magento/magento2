<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block;

use Mtf\Block\Block;
use Mtf\Client\Element\Locator;

/**
 * Class Search
 * Block for search field
 */
class Search extends Block
{
    /**
     * Selector matches found - "Suggest Search"
     *
     * @var string
     */
    protected $searchAutocomplete = './/div[@id="search_autocomplete"]//li[text()="%s"]';

    /**
     * Selector number of matches for a given row
     *
     * @var string
     */
    protected $searchItemAmount = '/span[contains(@class,"amount") and text()="%d"]';

    /**
     * Search field
     *
     * @var string
     */
    protected $searchInput = '#search';

    /**
     * Search button
     *
     * @var string
     */
    private $searchButton = '[title="Search"]';

    /**
     * Search button
     *
     * @var string
     */
    protected $placeholder = '//input[@id="search" and contains(@placeholder, "%s")]';

    /**
     * Css selector advanced search button
     *
     * @var string
     */
    protected $advancedSearchSelector = '.action.advanced';

    /**
     * Search products by a keyword
     *
     * @param string $keyword
     * @return void
     *
     * @SuppressWarnings(PHPMD.ConstructorWithNameAsEnclosingClass)
     */
    public function search($keyword)
    {
        $this->fillSearch($keyword);
        $this->_rootElement->find($this->searchButton, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Fills the search field
     *
     * @param string $text
     * @return void
     */
    public function fillSearch($text)
    {
        $this->_rootElement->find($this->searchInput, Locator::SELECTOR_CSS)->setValue($text);
    }

    /**
     * Check that placeholder contains text
     *
     * @param string $placeholderText
     * @return bool
     */
    public function isPlaceholderContains($placeholderText)
    {
        $field = $this->_rootElement->find(
            sprintf($this->placeholder, $placeholderText),
            Locator::SELECTOR_XPATH
        );
        return $field->isVisible();
    }

    /**
     * Checking block visibility "Suggest Search"
     *
     * @param string $text
     * @param int|null $amount
     * @return bool
     */
    public function isSuggestSearchVisible($text, $amount = null)
    {
        $searchAutocomplete = sprintf($this->searchAutocomplete, $text);
        if ($amount !== null) {
            $searchAutocomplete .= sprintf($this->searchItemAmount, $amount);
        }

        $rootElement = $this->_rootElement;
        return (bool)$this->_rootElement->waitUntil(
            function () use ($rootElement, $searchAutocomplete) {
                return $rootElement->find($searchAutocomplete, Locator::SELECTOR_XPATH)->isVisible() ? true : null;
            }
        );
    }

    /**
     * Click advanced search button
     *
     * @return void
     */
    public function clickAdvancedSearchButton()
    {
        $this->_rootElement->find($this->advancedSearchSelector)->click();
    }
}
