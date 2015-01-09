<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\ProductDetails;

use Mtf\Client\Driver\Selenium\Element\MultisuggestElement;
use Mtf\Client\Element\Locator;

/**
 * Typified element class for category element.
 */
class CategoryIds extends MultisuggestElement
{
    /**
     * Selector suggest input.
     *
     * @var string
     */
    protected $suggest = '#category_ids-suggest';

    /**
     * Selector for suggest element.
     *
     * @var string
     */
    protected $suggestElement = '.mage-suggest.category-select';

    /**
     * Selector item of search result.
     *
     * @var string
     */
    protected $resultItem = './/li/a/span[@class="category-label"][text()="%s"]';

    /**
     * Selector for click on top page.
     *
     * @var string
     */
    protected $top = './ancestor::body//*[@class="page-main-actions"]';

    /**
     * Set value.
     *
     * @param array|string $values
     * @return void
     */
    public function setValue($values)
    {
        $this->find($this->top, Locator::SELECTOR_XPATH)->click();
        $this->waitInitElement();
        parent::setValue($values);
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
        $selector = $this->suggestElement;

        $browser->waitUntil(
            function () use ($browser, $selector) {
                return $browser->find($selector)->isVisible() ? true : null;
            }
        );
    }
}
