<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Product\ProductList;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Class BottomToolbar
 * Bottom toolbar the product list page
 */
class BottomToolbar extends Block
{
    /**
     * Selector next active element
     *
     * @var string
     */
    protected $nextPageSelector = '.item.current + .item a';

    /**
     * Selector previous element
     *
     * @var string
     */
    protected $previousPageSelector = '.item.pages-item-previous';

    /**
     * Selector limiter block
     *
     * @var string
     */
    protected $optionBlockSelector = '.control';

    /**
     * Selector option element
     *
     * @var string
     */
    protected $optionSelector = './/option';

    /**
     * Go to the next page
     *
     * @return bool
     */
    public function nextPage()
    {
        $nextPageItem = $this->_rootElement->find($this->nextPageSelector);
        if ($nextPageItem->isVisible()) {
            $nextPageItem->click();
            return true;
        }
        return false;
    }

    /**
     * Go to the previous page
     *
     * @return bool
     */
    public function previousPage()
    {
        $previousPageItem = $this->_rootElement->find($this->previousPageSelector);
        if ($previousPageItem->isVisible()) {
            $previousPageItem->click();
            return true;
        }
        return false;
    }

    /**
     * Set value for limiter element by index
     *
     * @param int $index
     * @return $this
     */
    public function setLimiterValueByIndex($index)
    {
        $options = $this->_rootElement->getElements($this->optionSelector, Locator::SELECTOR_XPATH);
        if (isset($options[$index])) {
            $options[$index]->click();
        }
        return $this;
    }

    /**
     * Get value for limiter element by index
     *
     * @param int $index
     * @return int|null
     */
    public function getLimitedValueByIndex($index)
    {
        $options = $this->_rootElement->getElements($this->optionSelector, Locator::SELECTOR_XPATH);
        if (isset($options[$index])) {
            return $options[$index]->getValue();
        }
        return null;
    }
}
