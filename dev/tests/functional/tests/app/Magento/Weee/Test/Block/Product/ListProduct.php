<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Test\Block\Product;

use Magento\Mtf\Client\Locator;

/**
 * Product list
 */
class ListProduct extends \Magento\Catalog\Test\Block\Product\ListProduct
{
    /**
     * This member holds the class name for the fpt block found inside the product details.
     *
     * @var string
     */
    protected $fptBlockClass = '.price-box .weee [data-label="%s"]';

    /**
     * This method returns the fpt box block for the named product.
     *
     * @param string $productName
     * @param string $fptLabel
     * @return \Magento\Weee\Test\Block\Product\Fpt
     */
    public function getProductFptBlock($productName, $fptLabel)
    {
        $element = $this->getProductDetailsElement($productName)
            ->find(sprintf($this->fptBlockClass, $fptLabel), Locator::SELECTOR_CSS);
        return $this->blockFactory->create(
            'Magento\Weee\Test\Block\Product\Fpt',
            ['element' => $element]
        );
    }
}
