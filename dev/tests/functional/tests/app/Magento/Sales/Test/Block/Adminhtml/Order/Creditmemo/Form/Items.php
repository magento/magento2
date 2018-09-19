<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Creditmemo\Form;

use Magento\Sales\Test\Block\Adminhtml\Order\Creditmemo\Form\Items\Product;
use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

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
     * @param string $productSku
     * @return Product
     */
    public function getItemProductBlock($productSku)
    {
        $selector = sprintf($this->productItems, $productSku);
        return $this->blockFactory->create(
            \Magento\Sales\Test\Block\Adminhtml\Order\Creditmemo\Form\Items\Product::class,
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
        $button = $this->_rootElement->find($this->updateQty);
        if (!$button->isDisabled()) {
            $button->click();
        }
    }
}
