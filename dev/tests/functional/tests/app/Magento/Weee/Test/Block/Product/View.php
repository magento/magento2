<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Weee\Test\Block\Product;

use Mtf\Client\Element\Locator;

/**
 * Product view block on the product page
 */
class View extends \Magento\Catalog\Test\Block\Product\View
{
    /**
     * Selector for fpt block
     *
     * @var string
     */
    protected $fptBlock = '.price-box .weee [data-label="%s"]';

    /**
     * Get block fpt
     *
     * @param string $fptLabel
     * @return \Magento\Weee\Test\Block\Product\Fpt
     */
    public function getFptBlock($fptLabel)
    {
        return $this->blockFactory->create(
            'Magento\Weee\Test\Block\Product\Fpt',
            ['element' => $this->_rootElement->find(sprintf($this->fptBlock, $fptLabel), Locator::SELECTOR_CSS)]
        );
    }
}
