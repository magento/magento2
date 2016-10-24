<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Checkout\Controller\Cart
 */
namespace Magento\Checkout\Controller;

/**
 * @magentoDbIsolation enabled
 */
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

        $this->dispatch(
            'checkout/cart/configure/id/' . $quoteItem->getId() . '/product_id/' . $quoteItem->getProduct()->getId()
        );
        $response = $this->getResponse();

        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_ERROR);

        $this->assertSelectCount(
            'button[type="submit"][title="Update Cart"]',
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

        $this->dispatch(
            'checkout/cart/configure/id/' . $quoteItem->getId() . '/product_id/' . $quoteItem->getProduct()->getId()
        );
        $response = $this->getResponse();

        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_ERROR);

        $this->assertSelectCount(
            'button[type="submit"][title="Update Cart"]',
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

        $this->dispatch(
            'checkout/cart/configure/id/' . $quoteItem->getId() . '/product_id/' . $quoteItem->getProduct()->getId()
        );
        $response = $this->getResponse();

        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_ERROR);

        $this->assertSelectCount(
            'button[type="submit"][title="Update Cart"]',
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

        $this->dispatch(
            'checkout/cart/configure/id/' . $quoteItem->getId() . '/product_id/' . $quoteItem->getProduct()->getId()
        );
        $response = $this->getResponse();

        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_ERROR);

        $this->assertSelectCount(
            'button[type="submit"][title="Update Cart"]',
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
        $postData = [
            'cart' => [$quoteItem->getId() => ['qty' => $updatedQuantity]],
            'update_cart_action' => 'update_qty',
            'form_key' => $formKey->getFormKey(),
        ];
        $this->getRequest()->setPostValue($postData);
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
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->_objectManager->create('Magento\Quote\Model\Quote');
        $quote->load($checkoutSession->getQuote()->getId());
        $quoteItem = $this->_getQuoteItemIdByProductId($quote, 1);
        $this->assertEquals($updatedQuantity, $quoteItem->getQty(), "Invalid quote item quantity");
    }

    /**
     * Gets \Magento\Quote\Model\Quote\Item from \Magento\Quote\Model\Quote by product id
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param $productId
     * @return \Magento\Quote\Model\Quote\Item|null
     */
    private function _getQuoteItemIdByProductId($quote, $productId)
    {
        /** @var $quoteItems \Magento\Quote\Model\Quote\Item[] */
        $quoteItems = $quote->getAllItems();
        foreach ($quoteItems as $quoteItem) {
            if ($productId == $quoteItem->getProductId()) {
                return $quoteItem;
            }
        }
        return null;
    }

    /**
     * Test for \Magento\Checkout\Controller\Cart::execute() with simple product
     *
     * @param string $area
     * @param string $expectedPrice
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoAppIsolation enabled
     * @dataProvider addAddProductDataProvider
     */
    public function testAddToCartSimpleProduct($area, $expectedPrice)
    {
        $formKey = $this->_objectManager->get(\Magento\Framework\Data\Form\FormKey::class);
        $postData = [
            'qty' => '1',
            'product' => '1',
            'custom_price' => 1,
            'form_key' => $formKey->getFormKey(),
            'isAjax' => 1
        ];
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea($area);
        $this->getRequest()->setPostValue($postData);

        $quote =  $this->_objectManager->create(\Magento\Checkout\Model\Cart::class);
        /** @var \Magento\Checkout\Controller\Cart\Add $controller */
        $controller = $this->_objectManager->create(\Magento\Checkout\Controller\Cart\Add::class, [$quote]);
        $controller->execute();

        $this->assertContains(json_encode([]), $this->getResponse()->getBody());
        $items = $quote->getItems()->getItems();
        $this->assertTrue(is_array($items), 'Quote doesn\'t have any items');
        $this->assertCount(1, $items, 'Expected quote items not equal to 1');
        $item = reset($items);
        $this->assertEquals(1, $item->getProductId(), 'Quote has more than one product');
        $this->assertEquals($expectedPrice, $item->getPrice(), 'Expected product price failed');
    }

    /**
     * Data provider for testAddToCartSimpleProduct
     */
    public function addAddProductDataProvider()
    {
        return [
            'frontend' => ['frontend', 'expected_price' => 10],
            'adminhtml' => ['adminhtml', 'expected_price' => 1]
        ];
    }
}
