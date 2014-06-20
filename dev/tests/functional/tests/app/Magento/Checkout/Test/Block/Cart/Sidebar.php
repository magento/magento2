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
use Mtf\Client\Element\Locator;

/**
 * Class Sidebar
 * Mini shopping cart block
 */
class Sidebar extends Block
{
    /**
     * Quantity input selector
     *
     * @var string
     */
    protected $qty = '//*[@class="product"]/*[@title="%s"]/following-sibling::*//*[@class="value qty"]';

    /**
     * Mini cart link selector
     *
     * @var string
     */
    protected $cartLink = 'a.showcart';

    /**
     * Mini cart content selector
     *
     * @var string
     */
    protected $cartContent = 'div.minicart';

    /**
     * Open mini cart
     *
     * @return void
     */
    public function openMiniCart()
    {
        if (!$this->_rootElement->find($this->cartContent)->isVisible()) {
            $this->_rootElement->find($this->cartLink)->click();
        }
    }

    /**
     * Get product quantity
     *
     * @param string $productName
     * @return string
     */
    public function getProductQty($productName)
    {
        $this->openMiniCart();
        $productQty = sprintf($this->qty, $productName);
        return $this->_rootElement->find($productQty, Locator::SELECTOR_XPATH)->getText();
    }
}
