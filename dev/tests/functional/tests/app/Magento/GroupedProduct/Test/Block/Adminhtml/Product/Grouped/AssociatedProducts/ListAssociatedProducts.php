<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Block\Adminhtml\Product\Grouped\AssociatedProducts;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

/**
 * List associated products on product page.
 */
class ListAssociatedProducts extends Form
{
    /**
     * Selector with item product.
     *
     * @var string
     */
    protected $itemProduct = '//table[contains(@data-role,"grid")]/tbody/tr[%d]';

    /**
     * Getting block products
     *
     * @param string $index
     * @return ListAssociatedProducts\Product
     */
    private function getProductBlock($index)
    {
        $className = 'Magento\GroupedProduct\Test\Block\Adminhtml\Product\\' .
            'Grouped\AssociatedProducts\ListAssociatedProducts\Product';
        return $this->blockFactory->create(
            $className,
            ['element' => $this->_rootElement->find(sprintf($this->itemProduct, $index), Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Filling options products
     *
     * @param array $data
     * @param int $index
     * @return void
     */
    public function fillProductOptions(array $data, $index)
    {
        $this->getProductBlock($index)->fillOption($data);
    }

    /**
     * Get options products
     *
     * @param array $data
     * @param int $index
     * @return array
     */
    public function getProductOptions(array $data, $index)
    {
        return $this->getProductBlock($index)->getOption($data);
    }
}
