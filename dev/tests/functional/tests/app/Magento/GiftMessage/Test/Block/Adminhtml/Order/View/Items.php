<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Block\Adminhtml\Order\View;

use Magento\GiftMessage\Test\Block\Adminhtml\Order\View\Items\ItemProduct;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Class Items
 * Adminhtml GiftMessage order view items block.
 */
class Items extends \Magento\Sales\Test\Block\Adminhtml\Order\View\Items
{
    /**
     * Item product selector.
     *
     * @var string
     */
    protected $itemProduct = '//tbody[*[td//*[normalize-space(text())="%s"]]]';

    /**
     * Get item product block.
     *
     * @param InjectableFixture $product
     * @return ItemProduct
     */
    public function getItemProduct(InjectableFixture $product)
    {
        return $this->blockFactory->create(
            'Magento\GiftMessage\Test\Block\Adminhtml\Order\View\Items\ItemProduct',
            [
                'element' => $this->_rootElement->find(
                    sprintf($this->itemProduct, $product->getName()),
                    Locator::SELECTOR_XPATH
                )
            ]
        );
    }
}
