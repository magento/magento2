<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Block\Customer;

use Mtf\Block\Block;

/**
 * Class Wishlist
 * Wish list details block in "My Wish List" page
 */
class Wishlist extends Block
{
    /**
     * "Share Wish List" button selector
     *
     * @var string
     */
    protected $shareWishList = '[name="save_and_share"]';

    /**
     * Product items selector
     *
     * @var string
     */
    protected $productItems = '.product-items';

    /**
     * Selector for 'Add to Cart' button
     *
     * @var string
     */
    protected $addToCart = '.action.tocart';

    /**
     * Button 'Update Wish List' css selector
     *
     * @var string
     */
    protected $updateButton = '.action.update';

    /**
     * Empty block css selector
     *
     * @var string
     */
    protected $empty = '.message.info.empty';

    /**
     * Click button "Share Wish List"
     *
     * @return void
     */
    public function clickShareWishList()
    {
        $this->_rootElement->find($this->shareWishList)->click();
    }

    /**
     * Get items product block
     *
     * @return \Magento\Wishlist\Test\Block\Customer\Wishlist\Items
     */
    public function getProductItemsBlock()
    {
        return $this->blockFactory->create(
            'Magento\Wishlist\Test\Block\Customer\Wishlist\Items',
            ['element' => $this->_rootElement->find($this->productItems)]
        );
    }

    /**
     * Click button 'Add To Cart'
     *
     * @return void
     */
    public function clickAddToCart()
    {
        $this->_rootElement->find($this->addToCart)->click();
    }

    /**
     * Click button 'Update Wish List'
     *
     * @return void
     */
    public function clickUpdateWishlist()
    {
        $this->_rootElement->find($this->updateButton)->click();
    }

    /**
     * Check empty block visible
     *
     * @return bool
     */
    public function isEmptyBlockVisible()
    {
        return $this->_rootElement->find($this->empty)->isVisible();
    }
}
