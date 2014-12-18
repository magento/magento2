<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order;

use Magento\Sales\Test\Block\Adminhtml\Order\AbstractForm\Product;
use Mtf\Block\Block;
use Mtf\Fixture\FixtureInterface;

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
