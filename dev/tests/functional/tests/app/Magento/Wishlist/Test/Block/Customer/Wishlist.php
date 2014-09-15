<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Wishlist\Test\Block\Customer;

use Mtf\Block\Block;

/**
 * Class Wishlist
 * Wish list details block in "My account"
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
}
