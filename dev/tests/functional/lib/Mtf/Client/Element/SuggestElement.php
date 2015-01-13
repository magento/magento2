<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mtf\Client\Element;

use Mtf\Client\Locator;

/**
 * Class SuggestElement
 * General class for suggest elements.
 */
class SuggestElement extends SimpleElement
{
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
     * Set value
     *
     * @param string $value
     * @return void
     */
    public function setValue($value)
    {
        $this->eventManager->dispatchEvent(['set_value'], [__METHOD__, $this->getAbsoluteSelector()]);

        $this->find($this->suggest)->setValue($value);
        $this->waitResult();
        $this->find(sprintf($this->resultItem, $value), Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Wait for search result is visible
     *
     * @return void
     */
    public function waitResult()
    {
        $browser = $this;
        $selector = $this->searchResult;
        $browser->waitUntil(
            function () use ($browser, $selector) {
                return $browser->find($selector)->isVisible() ? true : null;
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
