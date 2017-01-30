<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\Block\Adminhtml\Form;

use Magento\Sales\Test\Block\Adminhtml\Order\AbstractItemsNewBlock;
use Magento\Shipping\Test\Block\Adminhtml\Form\Items\Product;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Adminhtml items to ship block.
 */
class Items extends AbstractItemsNewBlock
{
    /**
     * Get item product block.
     *
     * @param FixtureInterface $product
     * @return Product
     */
    public function getItemProductBlock(FixtureInterface $product)
    {
        $selector = sprintf($this->productItem, $product->getSku());
        return $this->blockFactory->create(
            'Magento\Shipping\Test\Block\Adminhtml\Form\Items\Product',
            ['element' => $this->_rootElement->find($selector, Locator::SELECTOR_XPATH)]
        );
    }
}
