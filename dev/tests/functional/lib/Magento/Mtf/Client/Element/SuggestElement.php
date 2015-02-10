<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Client\Element;

use Magento\Mtf\Client\Locator;

/**
 * Class SuggestElement
 * General class for suggest elements.
 */
class SuggestElement extends SimpleElement
{
    /**
     * "Backspace" key code.
     */
    const BACKSPACE = "\xEE\x80\x83";

    /**
     * Selector suggest input
     *
     * @var string
     */
    protected $suggest = '.mage-suggest-inner > .search';

    /**
     * Selector search result
     *
     * @var string
     */
    protected $searchResult = '.mage-suggest-dropdown';

    /**
     * Selector item of search result
     *
     * @var string
     */
    protected $resultItem = './/ul/li/a[text()="%s"]';

    /**
     * Suggest state loader
     *
     * @var string
     */
    protected $suggestStateLoader = '.mage-suggest-state-loading';

    /**
     * Set value
     *
     * @param string $value
     * @return void
     */
    public function setValue($value)
    {
        $this->eventManager->dispatchEvent(['set_value'], [__METHOD__, $this->getAbsoluteSelector()]);

        $this->clear();

        if ($value == '') {
            return;
        }
        foreach (str_split($value) as $symbol) {
            $input = $this->find($this->suggest);
            $input->click();
            $input->keys([$symbol]);
            $this->waitResult();
            $searchedItem = $this->find(sprintf($this->resultItem, $value), Locator::SELECTOR_XPATH);
            if ($searchedItem->isVisible()) {
                $searchedItem->click();
                break;
            }
        }
    }

    /**
     * Clear value of element.
     *
     * @return void
     */
    protected function clear()
    {
        $element = $this->find($this->suggest);
        while ($element->getValue() != '') {
            $element->keys([self::BACKSPACE]);
        }
    }

    /**
     * Wait for search result is visible
     *
     * @return void
     */
    public function waitResult()
    {
        $browser = $this;
        $selector = $this->suggestStateLoader;
        $browser->waitUntil(
            function () use ($browser, $selector) {
                $element = $browser->find($selector);
                return $element->isVisible() == false ? true : null;
            }
        );
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        $this->eventManager->dispatchEvent(['get_value'], [__METHOD__, $this->getAbsoluteSelector()]);

        return $this->find($this->suggest)->getValue();
    }

    /**
     * Checking exist value in search result
     *
     * @param string $value
     * @return bool
     */
    public function isExistValueInSearchResult($value)
    {
        $searchResult = $this->find($this->searchResult);

        $this->find($this->suggest)->setValue($value);
        $this->waitResult();
        if (!$searchResult->isVisible()) {
            return false;
        }

        return $searchResult->find(sprintf($this->resultItem, $value), Locator::SELECTOR_XPATH)->isVisible();
    }
}
