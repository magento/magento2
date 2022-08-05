<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Adminhtml\Cart\Product\Composite\Cart;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
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

    /** @var int */
    private $baseWebsiteId;

    /** @var SerializerInterface */
    private $json;

    /** @inheritdoc */
    public function setUp(): void
    {
        parent::setUp();
        $this->quoteItemCollectionFactory = $this->_objectManager->get(CollectionFactory::class);
        $this->baseWebsiteId = (int)$this->_objectManager->get(StoreManagerInterface::class)
            ->getWebsite('base')
            ->getId();
        $this->json = $this->_objectManager->get(SerializerInterface::class);
    }

    /**
     * @return void
     */
    public function testConfigureActionNoCustomerId(): void
    {
        $this->dispatchCompositeCartConfigure();
        $this->assertEquals(
            [
                'error' => true,
                'message' => "The customer ID isn't defined.",
            ],
            $this->json->unserialize($this->getResponse()->getBody())
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
            'website_id' => $this->baseWebsiteId,
        ]);
        $this->assertEquals(
            [
                'error' => true,
                'message' => "The quote items are incorrect. Verify the quote items and try again.",
            ],
            $this->json->unserialize($this->getResponse()->getBody())
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
        $itemsCollection = $this->quoteItemCollectionFactory->create();
        $itemId = $itemsCollection->getFirstItem()->getId();
        $this->assertNotEmpty($itemId);
        if (!$hasQuoteItem) {
            $itemId++;
        }
        $this->dispatchCompositeCartConfigure([
            'customer_id' => 1,
            'website_id' => $this->baseWebsiteId,
            'id' => $itemId,
        ]);
        $this->assertStringContainsString(
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
