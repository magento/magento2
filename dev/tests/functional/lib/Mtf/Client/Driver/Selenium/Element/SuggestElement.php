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

use Mtf\Client\Element\Locator;
use Mtf\Client\Driver\Selenium\Element;

/**
 * Class SuggestElement
 * General class for suggest elements.
 */
class SuggestElement extends Element
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
    protected $resultItem = './/*[contains(@class,"mage-suggest-dropdown")]/ul/li/a[text()="%s"]';

    /**
     * Set value
     *
     * @param string $value
     * @return void
     */
    public function setValue($value)
    {
        $this->_eventManager->dispatchEvent(['set_value'], [__METHOD__, $this->getAbsoluteSelector()]);

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
        $this->_eventManager->dispatchEvent(['get_value'], [(string) $this->_locator]);
        
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
