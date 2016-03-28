<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order;

use Magento\Sales\Test\Block\Adminhtml\Order\AbstractForm\Product;
use Magento\Mtf\Block\Block;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class AbstractItemsNewBlock
 * Items block on Credit Memo, Invoice, Shipment new pages
 */
abstract class AbstractItemsNewBlock extends Block
{
    /**
     * Item product row selector
     *
     * @var string
     */
    protected $productItem = '//tr[contains(.,"%s")]';

    /**
     * 'Update Qty's' button css selector
     *
     * @var string
     */
    protected $updateQty = '.update-button';

    /**
     * Get item product block
     *
     * @param FixtureInterface $product
     * @return Product
     */
    abstract public function getItemProductBlock(FixtureInterface $product);

    /**
     * Click update qty button
     *
     * @return void
     */
    public function clickUpdateQty()
    {
        $this->_rootElement->find($this->updateQty)->click();
    }
}
