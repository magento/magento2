<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Adminhtml\Cart\Product\Composite\Cart;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Tests for configure quote item in customer shopping cart.
 *
 * @magentoAppArea adminhtml
 */
class ConfigureTest extends AbstractBackendController
{
    /** @var CollectionFactory */
    private $quoteItemCollectionFactory;

    /** @inheritdoc */
    public function setUp()
    {
        parent::setUp();
        $this->quoteItemCollectionFactory = $this->_objectManager->get(CollectionFactory::class);
    }

    /**
     * @return void
     */
    public function testConfigureActionNoCustomerId(): void
    {
        $this->dispatchCompositeCartConfigure();
        $this->assertEquals(
            '{"error":true,"message":"The customer ID isn\'t defined."}',
            $this->getResponse()->getBody()
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @return void
     */
    public function testConfigureNoQuoteId(): void
    {
        $this->dispatchCompositeCartConfigure([
            'customer_id' => 1,
            'website_id' => 1,
        ]);
        $this->assertEquals(
            '{"error":true,"message":"The quote items are incorrect. Verify the quote items and try again."}',
            $this->getResponse()->getBody()
        );
    }

    /**
     * @dataProvider configureWithQuoteProvider
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/quote.php
     * @param bool $hasQuoteItem
     * @param string $expectedResponseBody
     * @return void
     */
    public function testConfigureWithQuote(bool $hasQuoteItem, string $expectedResponseBody): void
    {
        $items = $this->quoteItemCollectionFactory->create();
        $itemId = $items->getAllIds()[0];
        if (!$hasQuoteItem) {
            $itemId++;
        }
        $this->dispatchCompositeCartConfigure([
            'customer_id' => 1,
            'website_id' => 1,
            'id' => $itemId,
        ]);
        $this->assertContains(
            $expectedResponseBody,
            $this->getResponse()->getBody()
        );
    }

    /**
     * Create configure with quote provider
     *
     * @return array
     */
    public function configureWithQuoteProvider(): array
    {
        return [
            'with_quote_item_id' => [
                'has_quote_item' => true,
                'expected_response_body' => '<input id="product_composite_configure_input_qty"'
                    . ' class="input-text admin__control-text qty" type="text" name="qty" value="1">',
            ],
            'without_quote_item_id' => [
                'has_quote_item' => false,
                'expected_response_body' => '{"error":true,"message":"The quote items are incorrect.'
                    . ' Verify the quote items and try again."}',
            ],
        ];
    }

    /**
     * Dispatch configure quote item in customer shopping cart
     * using backend/customer/cart_product_composite_cart/configure action.
     *
     * @param array $params
     * @param array $postValue
     * @return void
     */
    private function dispatchCompositeCartConfigure(array $params = [], array $postValue = []): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setParams($params);
        $this->getRequest()->setPostValue($postValue);
        $this->dispatch('backend/customer/cart_product_composite_cart/configure');
    }
}
