<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Wishlist\Test\Block\Customer\Wishlist;

use Magento\Wishlist\Test\Block\Customer\Wishlist\Items\Product;
use Mtf\Block\Block;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;
use Mtf\Fixture\FixtureInterface;

/**
 * Class Items
 * Customer wishlist items block on frontend
 */
class Items extends Block
{
    /**
     * Item product block
     *
     * @var string
     */
    protected $itemBlock = '//li[.//a[contains(.,"%s")]]';

    /**
     * Selector for 'Remove item' button
     *
     * @var string
     */
    protected $remove = '[data-role="remove"]';

    /**
     * Get item product block
     *
     * @param FixtureInterface $product
     * @return Product
     */
    public function getItemProduct(FixtureInterface $product)
    {
        $productBlock = sprintf($this->itemBlock, $product->getName());
        return $this->blockFactory->create(
            'Magento\Wishlist\Test\Block\Customer\Wishlist\Items\Product',
            ['element' => $this->_rootElement->find($productBlock, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Remove all products from wish list
     *
     * @return void
     */
    public function removeAllProducts()
    {
        while ($this->_rootElement->find($this->remove)->isVisible()) {
            $this->_rootElement->find($this->remove)->click();
            $this->reinitRootElement();
        }
    }
}
