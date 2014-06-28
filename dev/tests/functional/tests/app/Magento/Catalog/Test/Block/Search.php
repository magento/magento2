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
