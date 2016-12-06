<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Links;

use Magento\Mtf\Block\Block;

/**
 * Products compare link block.
 */
class CompareLink extends Block
{
    /**
     * Locator value for qty of Products in Compare list.
     *
     * @var string
     */
    protected $qtyCompareProducts = '.compare .counter.qty';

    /**
     * Locator value for Compare Products link.
     *
     * @var string
     */
    protected $linkCompareProducts = '[data-role="compare-products-link"] a.compare';

    /**
     * Get qty of Products in Compare list.
     *
     * @return string
     */
    public function getQtyInCompareList()
    {
        $this->waitForElementVisible($this->qtyCompareProducts);
        $compareProductLink = $this->_rootElement->find($this->qtyCompareProducts);
        preg_match_all('/^\d+/', $compareProductLink->getText(), $matches);
        return $matches[0][0];
    }

    /**
     * Wait for compare products link to appear
     *
     * @return void
     */
    public function waitForCompareProductsLinks()
    {
        $this->waitForElementVisible($this->linkCompareProducts);
    }
}
