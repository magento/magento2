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
 
namespace Magento\Checkout\Test\Block\Cart;

use Mtf\Block\Block;

/**
 * Class AbstractCartItem
 * Base product item block on checkout page
 */
class AbstractCartItem extends Block
{
    /**
     * Selector for product name
     *
     * @var string
     */
    protected $productName = '.product-item-name > a';

    /**
     * Selector for unit price
     *
     * @var string
     */
    protected $price = './/td[@class="col price"]/*[@class="price-excluding-tax"]/span';

    /**
     * Quantity input selector
     *
     * @var string
     */
    protected $qty = './/input[@type="number" and @title="Qty"]';

    /**
     * Cart item sub-total xpath selector
     *
     * @var string
     */
    protected $subtotalPrice = './/td[@class="col subtotal"]//*[@class="price-excluding-tax"]//span[@class="price"]';

    /**
     *  Selector for options block
     *
     * @var string
     */
    protected $optionsBlock = './/dl[@class="cart-item-options"]';

    /**
     * Escape currency in price
     *
     * @param string $price
     * @return string|null
     */
    protected function escapeCurrency($price)
    {
        preg_match("/^\\D*\\s*([\\d,\\.]+)\\s*\\D*$/", $price, $matches);
        return (isset($matches[1])) ? $matches[1] : null;
    }
}
