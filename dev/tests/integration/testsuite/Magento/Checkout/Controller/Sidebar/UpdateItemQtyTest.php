<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Controller\Sidebar;

use Magento\Checkout\Model\Session;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

/**
 * Tests update item quantity controller.
 */
class UpdateItemQtyTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @var Json
     */
    private $json;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp()
    {
        parent::setUp();

        $this->json = $this->_objectManager->create(Json::class);
        $this->session = $this->_objectManager->create(Session::class);
        $this->productRepository = $this->_objectManager->create(ProductRepositoryInterface::class);
    }

    /**
     * Tests of cart validation when contains product with decimal quantity.
     *
     * @param string $requestQuantity
     * @param array $expectedResponse
     *
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Checkout/_files/quote_with_simple_product_decimal_qty.php
     * @dataProvider executeWithDecimalQtyDataProvider
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testExecuteWithDecimalQty(string $requestQuantity, array $expectedResponse)
    {
        $product = $this->productRepository->get('simple_with_decimal_qty');
        $quote = $this->getQuote('decimal_quote_id');
        $quoteItem = $quote->getItemByProduct($product);
        $this->session->setQuoteId($quote->getId());

        $this->assertNotNull($quoteItem, 'Cannot get quote item for simple product');

        $request= [
            'item_id' => $quoteItem->getId(),
            'item_qty' => $requestQuantity
        ];

        $this->getRequest()->setPostValue($request);
        $this->dispatch('checkout/sidebar/updateItemQty');
        $response = $this->getResponse()->getBody();

        $this->assertEquals($this->json->unserialize($response), $expectedResponse);
    }

    /**
     * Variations of request data.
     * @returns array
     */
    public function executeWithDecimalQtyDataProvider(): array
    {
        return [
            [
                'requestQuantity' => '2.2',
                'response' => [
                    'success' => true,
                ]
            ],
            [
                'requestQuantity' => '2',
                'response' => [
                    'success' => false,
                    'error_message' => 'You can buy this product only in quantities of 1.1 at a time.']
            ],
        ];
    }

    /**
     * Retrieves quote by reserved order id.
     *
     * @param string $reservedOrderId
     * @return Quote
     */
    private function getQuote(string $reservedOrderId): Quote
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->_objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', $reservedOrderId)
            ->create();

        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->_objectManager->get(CartRepositoryInterface::class);
        $items = $quoteRepository->getList($searchCriteria)->getItems();

        return array_pop($items);
    }
}
