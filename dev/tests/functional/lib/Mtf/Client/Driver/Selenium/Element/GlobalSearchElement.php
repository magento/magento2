<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Mtf\Client\Driver\Selenium\Element;

use Mtf\Client\Driver\Selenium\Element;

/**
 * Class GlobalSearchElement
 * Typified element class for global search element
 */
class GlobalSearchElement extends Element
{
    /**
     * Selector suggest input
     *
     * @var string
     */
    protected $suggest = '.mage-suggest-inner > [class^="search"]';

    /**
     * Result dropdown selector
     *
     * @var string
     */
    protected $searchResult = '.search-global-menu';

    /**
     * Item selector of search result
     *
     * @var string
     */
    protected $resultItem = 'li';

    /**
     * Search icon selector
     *
     * @var string
     */
    protected $searchIcon = '[for="search-global"]';

    /**
     * Set value
     *
     * @param string $value
     * @return void
     */
    public function setValue($value)
    {
        $this->_eventManager->dispatchEvent(['set_value'], [__METHOD__, $this->getAbsoluteSelector()]);

        $this->find($this->searchIcon)->click();
        $this->find($this->suggest)->setValue($value);
        $this->waitResult();
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
     * @throws \BadMethodCallException
     */
    public function getValue()
    {
        throw new \BadMethodCallException('Not applicable for this class of elements (GlobalSearch)');
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
        if (!$searchResult->isVisible()) {
            return false;
        }
        $searchResults = $this->getSearchResults();
        return in_array($value, $searchResults);
    }

    /**
     * Get search results
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
            $resultText = explode("\n", $resultItem->getText())[0];
            $resultArray[] = $resultText;
        }
        return $resultArray;
    }
}
