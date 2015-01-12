<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mtf\Client\Driver\Selenium\Element;

use Mtf\Client\Element\Locator;
use Mtf\Client\Driver\Selenium\Element;

/**
 * Typified element class for global search element.
 */
class GlobalsearchElement extends Element
{
    /**
     * Search icon selector.
     *
     * @var string
     */
    protected $searchIcon = '[for="search-global"]';

    /**
     * Locator for initialized suggest container.
     *
     * @var string
     */
    protected $initializedSuggest = './/*[contains(@class,"search-global-field") and .//*[@class="mage-suggest"]]';

    /**
     * Selector for search input element.
     *
     * @var string
     */
    protected $searchInput = '#search-global';

    /**
     * Result dropdown selector.
     *
     * @var string
     */
    protected $searchResult = '.autocomplete-results';

    /**
     * Item selector of search result.
     *
     * @var string
     */
    protected $resultItem = 'li';

    /**
     * "Backspace" key code.
     */
    const BACKSPACE = "\xEE\x80\x83";

    /**
     * Set value.
     *
     * @param string $value
     * @return void
     */
    public function setValue($value)
    {
        $this->_eventManager->dispatchEvent(['set_value'], [__METHOD__, $this->getAbsoluteSelector()]);

        $this->waitInitElement();

        if (!$this->find($this->searchInput)->isVisible()) {
            $this->find($this->searchIcon)->click();
        }
        $this->selectWindow();
        $this->clear();
        $this->find($this->searchInput)->_getWrappedElement()->value($value);
        $this->selectWindow();

        $this->waitResult();
    }

    /**
     * Clear value of element.
     *
     * @return void
     */
    protected function clear()
    {
        $element = $this->find($this->searchInput);
        while ('' != $element->getValue()) {
            $element->keys([self::BACKSPACE]);
        }
    }

    /**
     * Select to last window.
     *
     * @return void
     */
    protected function selectWindow()
    {
        $windowHandles = $this->_driver->windowHandles();
        $this->_driver->window(end($windowHandles));
    }

    /**
     * Wait init search suggest container.
     *
     * @return void
     * @throws \Exception
     */
    protected function waitInitElement()
    {
        $browser = clone $this;
        $selector = $this->initializedSuggest;

        $browser->waitUntil(
            function () use ($browser, $selector) {
                return $browser->find($selector, Locator::SELECTOR_XPATH)->isVisible() ? true : null;
            }
        );
    }

    /**
     * Wait for search result is visible.
     *
     * @return void
     */
    public function waitResult()
    {
        $browser = clone $this;
        $selector = $this->searchResult;

        $browser->waitUntil(
            function () use ($browser, $selector) {
                if ($browser->find($selector)->isVisible()) {
                    return true;
                } else {
                    $browser->selectWindow();
                    return null;
                }
            }
        );
    }

    /**
     * Get value.
     *
     * @throws \BadMethodCallException
     */
    public function getValue()
    {
        throw new \BadMethodCallException('Not applicable for this class of elements (GlobalSearch)');
    }

    /**
     * Checking exist value in search result.
     *
     * @param string $value
     * @return bool
     */
    public function isExistValueInSearchResult($value)
    {
        $searchResult = $this->find($this->searchResult);
        if (!$searchResult->isVisible()) {
            return false;
        }
        $searchResults = $this->getSearchResults();
        return in_array($value, $searchResults);
    }

    /**
     * Get search results.
     *
     * @return array
     */
    protected function getSearchResults()
    {
        /** @var Element $searchResult */
        $searchResult = $this->find($this->searchResult);
        $resultItems = $searchResult->find($this->resultItem)->getElements();
        $resultArray = [];

        /** @var Element $resultItem */
        foreach ($resultItems as $resultItem) {
            $resultItemLink = $resultItem->find('a');
            $resultText = $resultItemLink->isVisible()
                ? trim($resultItemLink->getText())
                : trim($resultItem->getText());
            $resultArray[] = $resultText;
        }

        return $resultArray;
    }
}
