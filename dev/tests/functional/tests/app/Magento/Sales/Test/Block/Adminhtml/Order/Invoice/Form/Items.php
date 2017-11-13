<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Invoice\Form;

use Magento\Mtf\Client\Locator;
use Magento\Sales\Test\Block\Adminhtml\Order\AbstractItemsNewBlock;
use Magento\Sales\Test\Block\Adminhtml\Order\Invoice\Form\Items\Product;

/**
 * Block for items to invoice on new invoice page.
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
            \Magento\Sales\Test\Block\Adminhtml\Order\Invoice\Form\Items\Product::class,
            ['element' => $this->_rootElement->find($selector, Locator::SELECTOR_XPATH)]
        );
    }
}
