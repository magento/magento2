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
namespace Magento\Checkout\Controller;

class CartTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * Test for \Magento\Checkout\Controller\Cart::configureAction() with simple product
     *
     * @magentoDataFixture Magento/Checkout/_files/quote_with_simple_product.php
     */
    public function testConfigureActionWithSimpleProduct()
    {
        /** @var $session \Magento\Checkout\Model\Session  */
        $session = $this->_objectManager->create('Magento\Checkout\Model\Session');

        $quoteItem = $this->_getQuoteItemIdByProductId($session->getQuote(), 1);
        $this->assertNotNull($quoteItem, 'Cannot get quote item for simple product');

        $this->dispatch('checkout/cart/configure/id/' . $quoteItem->getId());
        $response = $this->getResponse();

        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_ERROR);

        $this->assertSelectCount(
            'button[type="button"][title="Update Cart"]',
            1,
            $response->getBody(),
            'Response for simple product doesn\'t contain "Update Cart" button'
        );
    }

    /**
     * Test for \Magento\Checkout\Controller\Cart::configureAction() with simple product and custom option
     *
     * @magentoDataFixture Magento/Checkout/_files/quote_with_simple_product_and_custom_option.php
     */
    public function testConfigureActionWithSimpleProductAndCustomOption()
    {
        /** @var $session \Magento\Checkout\Model\Session  */
        $session = $this->_objectManager->create('Magento\Checkout\Model\Session');

        $quoteItem = $this->_getQuoteItemIdByProductId($session->getQuote(), 1);
        $this->assertNotNull($quoteItem, 'Cannot get quote item for simple product with custom option');

        $this->dispatch('checkout/cart/configure/id/' . $quoteItem->getId());
        $response = $this->getResponse();

        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_ERROR);

        $this->assertSelectCount(
            'button[type="button"][title="Update Cart"]',
            1,
            $response->getBody(),
            'Response for simple product with custom option doesn\'t contain "Update Cart" button'
        );

        $this->assertSelectCount(
            'input.product-custom-option[type="text"]',
            1,
            $response->getBody(),
            'Response for simple product with custom option doesn\'t contain custom option input field'
        );
    }

    /**
     * Test for \Magento\Checkout\Controller\Cart::configureAction() with bundle product
     *
     * @magentoDataFixture Magento/Checkout/_files/quote_with_bundle_product.php
     */
    public function testConfigureActionWithBundleProduct()
    {
        /** @var $session \Magento\Checkout\Model\Session  */
        $session = $this->_objectManager->create('Magento\Checkout\Model\Session');

        $quoteItem = $this->_getQuoteItemIdByProductId($session->getQuote(), 3);
        $this->assertNotNull($quoteItem, 'Cannot get quote item for bundle product');

        $this->dispatch('checkout/cart/configure/id/' . $quoteItem->getId());
        $response = $this->getResponse();

        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_ERROR);

        $this->assertSelectCount(
            'button[type="button"][title="Update Cart"]',
            1,
            $response->getBody(),
            'Response for bundle product doesn\'t contain "Update Cart" button'
        );
    }

    /**
     * Test for \Magento\Checkout\Controller\Cart::configureAction() with downloadable product
     *
     * @magentoDataFixture Magento/Checkout/_files/quote_with_downloadable_product.php
     */
    public function testConfigureActionWithDownloadableProduct()
    {
        /** @var $session \Magento\Checkout\Model\Session  */
        $session = $this->_objectManager->create('Magento\Checkout\Model\Session');

        $quoteItem = $this->_getQuoteItemIdByProductId($session->getQuote(), 1);
        $this->assertNotNull($quoteItem, 'Cannot get quote item for downloadable product');

        $this->dispatch('checkout/cart/configure/id/' . $quoteItem->getId());
        $response = $this->getResponse();

        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_ERROR);

        $this->assertSelectCount(
            'button[type="button"][title="Update Cart"]',
            1,
            $response->getBody(),
            'Response for downloadable product doesn\'t contain "Update Cart" button'
        );

        $this->assertSelectCount(
            '#downloadable-links-list',
            1,
            $response->getBody(),
            'Response for downloadable product doesn\'t contain links for download'
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Checkout/_files/quote_with_simple_product.php
     * @magentoAppIsolation enabled
     */
    public function testUpdatePostAction()
    {
        /** Preconditions */
        $customerFromFixture = 1;
        $productId = 1;
        $originalQuantity = 1;
        $updatedQuantity = 2;
        /** @var $checkoutSession \Magento\Checkout\Model\Session  */
        $checkoutSession = $this->_objectManager->create('Magento\Checkout\Model\Session');
        $quoteItem = $this->_getQuoteItemIdByProductId($checkoutSession->getQuote(), $productId);

        /** @var \Magento\Framework\Data\Form\FormKey $formKey */
        $formKey = $this->_objectManager->get('Magento\Framework\Data\Form\FormKey');
        $postData = array(
            'cart' => array($quoteItem->getId() => array('qty' => $updatedQuantity)),
            'update_cart_action' => 'update_qty',
            'form_key' => $formKey->getFormKey()
        );
        $this->getRequest()->setPost($postData);
        /** @var $customerSession \Magento\Customer\Model\Session */
        $customerSession = $this->_objectManager->create('Magento\Customer\Model\Session');
        $customerSession->setCustomerId($customerFromFixture);

        $this->assertNotNull($quoteItem, 'Cannot get quote item for simple product');
        $this->assertEquals(
            $originalQuantity,
            $quoteItem->getQty(),
            "Precondition failed: invalid quote item quantity"
        );

        /** Execute SUT */
        $this->dispatch('checkout/cart/updatePost');

        /** Check results */
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->_objectManager->create('Magento\Sales\Model\Quote');
        $quote->load($checkoutSession->getQuote()->getId());
        $quoteItem = $this->_getQuoteItemIdByProductId($quote, 1);
        $this->assertEquals($updatedQuantity, $quoteItem->getQty(), "Invalid quote item quantity");
    }

    /**
     * Gets \Magento\Sales\Model\Quote\Item from \Magento\Sales\Model\Quote by product id
     *
     * @param \Magento\Sales\Model\Quote $quote
     * @param $productId
     * @return \Magento\Sales\Model\Quote\Item|null
     */
    private function _getQuoteItemIdByProductId($quote, $productId)
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
