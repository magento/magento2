<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\Block\Adminhtml\Form;

use Magento\Sales\Test\Block\Adminhtml\Order\AbstractItemsNewBlock;
use Magento\Shipping\Test\Block\Adminhtml\Form\Items\Product;
use Magento\Mtf\Client\Locator;

/**
 * Adminhtml items to ship block.
 */
class Items extends AbstractItemsNewBlock
{
    /**
     * Get item product block.
     *
     * @param string $productSku
     * @return Product
     */
    public function getItemProductBlock($productSku)
    {
        $selector = sprintf($this->productItem, $productSku);
        return $this->blockFactory->create(
            \Magento\Shipping\Test\Block\Adminhtml\Form\Items\Product::class,
            ['element' => $this->_rootElement->find($selector, Locator::SELECTOR_XPATH)]
        );
    }
}
