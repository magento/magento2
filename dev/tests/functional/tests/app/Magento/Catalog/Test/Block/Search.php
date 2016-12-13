<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Class Search
 * Block for "Search" section
 */
class Search extends Block
{
    /**
     * Locator value for matches found - "Suggest Search".
     *
     * @var string
     */
    protected $searchAutocomplete = './/div[@id="search_autocomplete"]//li[span[text()[normalize-space()="%s"]]]';

    /**
     * Locator value for given row matches amount.
     *
     * @var string
     */
    protected $searchItemAmount = '/span[contains(@class,"amount") and text()="%d"]';

    /**
     * Locator value for "Search" field.
     *
     * @var string
     */
    protected $searchInput = '#search';

    /**
     * Locator value for "Search" button.
     *
     * @var string
     */
    private $searchButton = '[title="Search"]';

    /**
     * Locator value for "Search" button placeholder.
     *
     * @var string
     */
    protected $placeholder = '//input[@id="search" and contains(@placeholder, "%s")]';

    /**
     * Locator value for list items.
     *
     * @var string
     */
    private $searchListItems = './/div[@id="search_autocomplete"]//li';

    /**
     * Locator value for body with aria-busy attribute.
     *
     * @var string
     */
    private $selectorAriaBusy = './/body[@aria-busy="false"]';

    /**
     * Perform search by a keyword.
     *
     * @param string $keyword
     * @param string|null $length
     * @return void
     */
    public function search($keyword, $length = null)
    {
        if ($length) {
            $keyword = substr($keyword, 0, $length);
        }
        $this->fillSearch($keyword);
        $this->_rootElement->find($this->searchButton)->click();
    }

    /**
     * Fill "Search" field with correspondent text.
     *
     * @param string $text
     * @return void
     */
    public function fillSearch($text)
    {
        $this->_rootElement->find($this->searchInput)->setValue($text);
        $this->waitUntilSearchPrepared();
    }

    /**
     * Wait until "Suggest Search" block will be prepared.
     *
     * @return bool
     */
    public function waitUntilSearchPrepared()
    {
        $this->browser->waitUntil(
            function () {
                $count = count($this->_rootElement->getElements($this->searchListItems, Locator::SELECTOR_XPATH));
                usleep(200);
                $newCount = count($this->_rootElement->getElements($this->searchListItems, Locator::SELECTOR_XPATH));
                return $this->browser->find($this->selectorAriaBusy, Locator::SELECTOR_XPATH)->isVisible()
                    && ($newCount == $count)
                    ? true
                    : null;
            }
        );
    }

    /**
     * Check if placeholder contains correspondent text or not.
     *
     * @param string $placeholderText
     * @return bool
     */
    public function isPlaceholderContains($placeholderText)
    {
        $field = $this->_rootElement->find(sprintf($this->placeholder, $placeholderText), Locator::SELECTOR_XPATH);
        return $field->isVisible();
    }

    /**
     * Check if "Suggest Search" block visible or not.
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
     * Click on suggested text.
     *
     * @param string $text
     * @return void
     */
    public function clickSuggestedText($text)
    {
        $searchAutocomplete = sprintf($this->searchAutocomplete, $text);
        $this->_rootElement->find($searchAutocomplete, Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Check if search field is visible.
     *
     * @return bool
     */
    public function isSearchVisible()
    {
        return $this->_rootElement->isVisible();
    }
}
