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
 * @category    Magento
 * @package     Magento_Checkout
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shopping cart interface
 *
 * @category    Magento
 * @package     Magento_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */

namespace Magento\Checkout\Model\Cart;

interface CartInterface
{
    /**
     * Add product to shopping cart (quote)
     *
     * @param   int|\Magento\Catalog\Model\Product $productInfo
     * @param   mixed                          $requestInfo
     * @return  \Magento\Checkout\Model\Cart\CartInterface
     */
    public function addProduct($productInfo, $requestInfo = null);

    /**
     * Save cart
     *
     * @abstract
     * @return \Magento\Checkout\Model\Cart\CartInterface
     */
    public function saveQuote();

    /**
     * Associate quote with the cart
     *
     * @abstract
     * @param $quote \Magento\Sales\Model\Quote
     * @return \Magento\Checkout\Model\Cart\CartInterface
     */
    public function setQuote(\Magento\Sales\Model\Quote $quote);

    /**
     * Get quote object associated with cart
     * @abstract
     * @return \Magento\Sales\Model\Quote
     */
    public function getQuote();
}
