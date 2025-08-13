<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Controller\Checkout;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\CartRepositoryInterface as QuoteRepository;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Test class for \Magento\Multishipping\Controller\Checkout
 *
 * @magentoAppArea frontend
 * @magentoDataFixture Magento/Sales/_files/quote.php
 * @magentoDataFixture Magento/Customer/_files/customer.php
 */
class CheckItemsTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @var Quote
     */
    private $quote;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Json
     */
    private $json;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->checkoutSession = $this->_objectManager->get(Session::class);
        $this->productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $this->json = $this->_objectManager->get(Json::class);
        $this->quote = $this->getQuote('test01');
        $this->checkoutSession->setQuoteId($this->quote->getId());
        $this->checkoutSession->setCartWasUpdated(false);
    }

    /**
     * Validator of quote items.
     *
     * @param array $requestQuantity
     * @param array $expectedResponse
     *
     * @magentoConfigFixture current_store multishipping/options/checkout_multiple 1
     * @magentoConfigFixture current_store multishipping/options/checkout_multiple_maximum_qty 200
     * @dataProvider requestDataProvider
     */
    public function testExecute($requestQuantity, $expectedResponse)
    {
        $this->loginCustomer();

        try {
            /** @var $product Product */
            $product = $this->productRepository->get('simple');
        } catch (\Exception $e) {
            $this->fail('No such product entity');
        }

        $quoteItem = $this->quote->getItemByProduct($product);
        $this->assertNotFalse($quoteItem, 'Cannot get quote item for simple product');

        $request = [];
        if (!empty($requestQuantity) && is_array($requestQuantity)) {
            $request= [
                'ship' => [
                    [$quoteItem->getId() => $requestQuantity],
                ]
            ];
        }

        $this->getRequest()->setPostValue($request);
        $this->dispatch('multishipping/checkout/checkItems');
        $response = $this->getResponse()->getBody();

        $this->assertEquals($expectedResponse, $this->json->unserialize($response));
    }

    /**
     * Authenticates customer and creates customer session.
     */
    private function loginCustomer()
    {
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        /** @var AccountManagementInterface $service */
        $service = $this->_objectManager->create(AccountManagementInterface::class);
        try {
            $customer = $service->authenticate('customer@example.com', 'password');
        } catch (LocalizedException $e) {
            $this->fail($e->getMessage());
        }
        /** @var CustomerSession $customerSession */
        $customerSession = $this->_objectManager->create(CustomerSession::class, [$logger]);
        $customerSession->setCustomerDataAsLoggedIn($customer);
    }

    /**
     * Gets quote by reserved order id.
     *
     * @param string $reservedOrderId
     * @return Quote
     */
    private function getQuote($reservedOrderId)
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->_objectManager->create(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', $reservedOrderId)
            ->create();
        /** @var QuoteRepository $quoteRepository */
        $quoteRepository = $this->_objectManager->get(QuoteRepository::class);
        $items = $quoteRepository->getList($searchCriteria)
            ->getItems();
        return array_pop($items);
    }

    /**
     * Variations of request data.
     * @returns array
     */
    public static function requestDataProvider(): array
    {
        return [
            [
                'requestQuantity' => [],
                'expectedResponse' => [
                    'success' => false,
                    'error_message' => 'We are unable to process your request. Please, try again later.'
                ]
            ],
            [
                'requestQuantity' => ['qty' => 2],
                'expectedResponse' => [
                    'success' => true,
                ]
            ],
            [
                'requestQuantity' => ['qty' => 101],
                'expectedResponse' => [
                    'success' => false,
                    'error_message' => 'Not enough items for sale']
            ],
            [
                'requestQuantity' => ['qty' => 230],
                'expectedResponse' => [
                    'success' => false,
                    'error_message' => 'Maximum qty allowed for Shipping to multiple addresses is 200']
            ],
        ];
    }
}
