<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Creditmemo\Form;

use Magento\Sales\Test\Block\Adminhtml\Order\Creditmemo\Form\Items\Product;
use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Credit Memo Items block on Credit Memo new page.
 */
class Items extends Block
{
    /**
     * Item product.
     *
     * @var string
     */
    protected $productItems = '//tr[contains(.,"%s")]';

    /**
     * 'Update Qty's' button css selector.
     *
     * @var string
     */
    protected $updateQty = '.update-button';

    /**
     * Get item product block.
     *
     * @param FixtureInterface $product
     * @return Product
     */
    public function getItemProductBlock(FixtureInterface $product)
    {
        $selector = sprintf($this->productItems, $product->getSku());
        return $this->blockFactory->create(
            'Magento\Sales\Test\Block\Adminhtml\Order\Creditmemo\Form\Items\Product',
            ['element' => $this->_rootElement->find($selector, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Click update qty button.
     *
     * @return void
     */
    public function clickUpdateQty()
    {
        $this->_rootElement->find($this->updateQty)->click();
    }
}
