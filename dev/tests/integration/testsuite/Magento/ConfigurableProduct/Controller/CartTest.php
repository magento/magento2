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

/**
 * Test class for \Magento\Checkout\Controller\Cart
 */
namespace Magento\ConfigurableProduct\Controller;

class CartTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * Test for \Magento\Checkout\Controller\Cart::configureAction() with configurable product
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/quote_with_configurable_product.php
     */
    public function testConfigureActionWithConfigurableProduct()
    {
        /** @var $session \Magento\Checkout\Model\Session  */
        $session = $this->_objectManager->create('Magento\Checkout\Model\Session');

        $quoteItem = $this->_getQuoteItemIdByProductId($session->getQuote(), 1);
        $this->assertNotNull($quoteItem, 'Cannot get quote item for configurable product');

        $this->dispatch('checkout/cart/configure/id/' . $quoteItem->getId());
        $response = $this->getResponse();

        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_ERROR);

        $this->assertSelectCount(
            'button[type="button"][title="Update Cart"]',
            1,
            $response->getBody(),
            'Response for configurable product doesn\'t contain "Update Cart" button'
        );

        $this->assertSelectCount(
            'select.super-attribute-select',
            1,
            $response->getBody(),
            'Response for configurable product doesn\'t contain select for super attribute'
        );
    }

    /**
     * Gets \Magento\Sales\Model\Quote\Item from \Magento\Sales\Model\Quote by product id
     *
     * @param \Magento\Sales\Model\Quote $quote
     * @param mixed $productId
     * @return \Magento\Sales\Model\Quote\Item|null
     */
    private function _getQuoteItemIdByProductId(\Magento\Sales\Model\Quote $quote, $productId)
    {
        /** @var $quoteItems \Magento\Sales\Model\Quote\Item[] */
        $quoteItems = $quote->getAllItems();
        foreach ($quoteItems as $quoteItem) {
            if ($productId == $quoteItem->getProductId()) {
                return $quoteItem;
            }
        }
        return null;
    }
}
