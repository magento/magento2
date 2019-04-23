<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Checkout\Controller\Cart
 */
namespace Magento\ConfigurableProduct\Controller;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Request\Http as HttpRequest;

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
        $session = $this->_objectManager->create(\Magento\Checkout\Model\Session::class);

        $productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $product = $productRepository->get('configurable');

        $quoteItem = $this->_getQuoteItemIdByProductId($session->getQuote(), $product->getId());
        $this->assertNotNull($quoteItem, 'Cannot get quote item for configurable product');

        $this->dispatch(
            'checkout/cart/configure/id/' . $quoteItem->getId() . '/product_id/' . $quoteItem->getProduct()->getId()
        );
        $response = $this->getResponse();

        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_ERROR);

        $this->assertSelectCount(
            'button[type="submit"][title="Update Cart"]',
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
     * Gets \Magento\Quote\Model\Quote\Item from \Magento\Quote\Model\Quote by product id
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param mixed $productId
     * @return \Magento\Quote\Model\Quote\Item|null
     */
    private function _getQuoteItemIdByProductId(\Magento\Quote\Model\Quote $quote, $productId)
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
     * Test for \Magento\Checkout\Controller\Cart\CouponPost::execute() with configurable product with last option.
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/quote_with_configurable_product_last_variation.php
     */
    public function testExecuteForConfigurableLastOption()
    {
        /** @var $session \Magento\Checkout\Model\Session */
        $session = $this->_objectManager->create(\Magento\Checkout\Model\Session::class);
        $quote = $session->getQuote();
        $quote->setData('trigger_recollect', 1)->setTotalsCollectedFlag(true);
        $inputData = [
            'remove' => 0,
            'coupon_code' => 'test',
        ];
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($inputData);
        $this->dispatch(
            'checkout/cart/couponPost/'
        );

        $this->assertSessionMessages(
            $this->equalTo(['The coupon code &quot;test&quot; is not valid.']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }
}
