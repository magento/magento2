<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Controller\Cart;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Message\MessageInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Integration tests for \Magento\Checkout\Controller\Cart\UpdateItemOptions class.
 */
class UpdateItemOptionsTest extends AbstractController
{
    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->formKey = $this->_objectManager->get(FormKey::class);
        $this->checkoutSession = $this->_objectManager->get(CheckoutSession::class);
        $this->productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * Tests that product is successfully updated in the shopping cart.
     *
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Checkout/_files/quote_with_simple_product.php
     */
    public function testUpdateProductOptionsInQuote()
    {
        $product = $this->productRepository->get('simple');
        $quoteItem = $this->checkoutSession->getQuote()->getItemByProduct($product);
        $postData = $this->preparePostData($product, $quoteItem);
        $this->dispatchUpdateItemOptionsRequest($postData);
        $this->assertTrue($this->getResponse()->isRedirect());
        $this->assertRedirect($this->stringContains('/checkout/cart/'));
        $message = (string)__(
            '%1 was updated in your shopping cart.',
            $product->getName()
        );
        $this->assertSessionMessages(
            $this->containsEqual($message),
            MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * Tests that product can't be updated with an empty shopping cart.
     *
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Checkout/_files/quote_with_simple_product.php
     */
    public function testUpdateProductOptionsWithEmptyQuote()
    {
        $product = $this->productRepository->get('simple');
        $quoteItem = $this->checkoutSession->getQuote()->getItemByProduct($product);
        $postData = $this->preparePostData($product, $quoteItem);
        $this->checkoutSession->clearQuote();
        $this->dispatchUpdateItemOptionsRequest($postData);
        $this->assertTrue($this->getResponse()->isRedirect());
        $this->assertRedirect($this->stringContains('/checkout/cart/'));
        $message = (string)__('The quote item isn&#039;t found. Verify the item and try again.');
        $this->assertSessionMessages(
            $this->containsEqual($message),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Prepare post data for the request.
     *
     * @param ProductInterface $product
     * @param QuoteItem|bool $quoteItem
     * @return array
     */
    private function preparePostData(ProductInterface $product, $quoteItem): array
    {
        return [
            'product' => $product->getId(),
            'selected_configurable_option' => '',
            'related_product' => '',
            'item' => $quoteItem->getId(),
            'form_key' => $this->formKey->getFormKey(),
            'qty' => '2',
        ];
    }

    /**
     * Perform request for updating product options in a quote item.
     *
     * @param array $postData
     * @return void
     */
    private function dispatchUpdateItemOptionsRequest(array $postData): void
    {
        $this->getRequest()->setPostValue($postData);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('checkout/cart/updateItemOptions/id/' . $postData['item']);
    }
}
