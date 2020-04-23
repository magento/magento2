<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Quote\Model\Quote;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Framework\Webapi\Rest\Request;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartRepositoryTest extends WebapiAbstract
{
    private static $mineCartUrl = '/V1/carts/mine';

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->filterBuilder = $this->objectManager->create(
            \Magento\Framework\Api\FilterBuilder::class
        );
        $this->sortOrderBuilder = $this->objectManager->create(
            \Magento\Framework\Api\SortOrderBuilder::class
        );
        $this->searchCriteriaBuilder = $this->objectManager->create(
            \Magento\Framework\Api\SearchCriteriaBuilder::class
        );
    }

    protected function tearDown(): void
    {
        try {
            /** @var CartRepositoryInterface $quoteRepository */
            $quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
            $cart = $this->getCart('test01');
            $quoteRepository->delete($cart);
        } catch (\InvalidArgumentException $e) {
            // Do nothing if cart fixture was not used
        }
        parent::tearDown();
    }

    /**
     * Retrieve quote by given reserved order ID
     *
     * @param string $reservedOrderId
     * @return \Magento\Quote\Model\Quote
     * @throws \InvalidArgumentException
     */
    private function getCart($reservedOrderId)
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', $reservedOrderId)
            ->create();

        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $items = $quoteRepository->getList($searchCriteria)->getItems();

        if (empty($items)) {
            throw new \InvalidArgumentException('There is no quote with provided reserved order ID.');
        }

        return array_pop($items);
    }

    /**
     * Tests successfull get cart web-api call.
     *
     * @magentoApiDataFixture Magento/Sales/_files/quote.php
     */
    public function testGetCart()
    {
        $cart = $this->getCart('test01');
        $cartId = $cart->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/' . $cartId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'quoteCartRepositoryV1',
                'serviceVersion' => 'V1',
                'operation' => 'quoteCartRepositoryV1Get',
            ],
        ];

        $requestData = ['cartId' => $cartId];
        $cartData = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals($cart->getId(), $cartData['id']);
        $this->assertEquals($cart->getCreatedAt(), $cartData['created_at']);
        $this->assertEquals($cart->getUpdatedAt(), $cartData['updated_at']);
        $this->assertEquals($cart->getIsActive(), $cartData['is_active']);
        $this->assertEquals($cart->getIsVirtual(), $cartData['is_virtual']);
        $this->assertEquals($cart->getOrigOrderId(), $cartData['orig_order_id']);
        $this->assertEquals($cart->getItemsCount(), $cartData['items_count']);
        $this->assertEquals($cart->getItemsQty(), $cartData['items_qty']);
        //following checks will be uncommented when all cart related services are ready
        $this->assertArrayHasKey('customer', $cartData);
        $this->assertTrue($cartData['customer_is_guest']);
        $this->assertArrayHasKey('currency', $cartData);
        $this->assertEquals($cart->getGlobalCurrencyCode(), $cartData['currency']['global_currency_code']);
        $this->assertEquals($cart->getBaseCurrencyCode(), $cartData['currency']['base_currency_code']);
        $this->assertEquals($cart->getQuoteCurrencyCode(), $cartData['currency']['quote_currency_code']);
        $this->assertEquals($cart->getStoreCurrencyCode(), $cartData['currency']['store_currency_code']);
        $this->assertEquals($cart->getBaseToGlobalRate(), $cartData['currency']['base_to_global_rate']);
        $this->assertEquals($cart->getBaseToQuoteRate(), $cartData['currency']['base_to_quote_rate']);
        $this->assertEquals($cart->getStoreToBaseRate(), $cartData['currency']['store_to_base_rate']);
        $this->assertEquals($cart->getStoreToQuoteRate(), $cartData['currency']['store_to_quote_rate']);
    }

    /**
     * Tests exception when cartId is not provided.
     *
     */
    public function testGetCartThrowsExceptionIfThereIsNoCartWithProvidedId()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No such entity with');

        $cartId = 9999;

        $serviceInfo = [
            'soap' => [
                'service' => 'quoteCartRepositoryV1',
                'serviceVersion' => 'V1',
                'operation' => 'quoteCartRepositoryV1Get',
            ],
            'rest' => [
                'resourcePath' => '/V1/carts/' . $cartId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
        ];

        $requestData = ['cartId' => $cartId];
        $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * Tests carts search.
     *
     * @magentoApiDataFixture Magento/Sales/_files/quote.php
     */
    public function testGetList()
    {
        $cart = $this->getCart('test01');

        // The following two filters are used as alternatives. The target cart does not match the first one.
        $grandTotalFilter = $this->filterBuilder->setField('grand_total')
            ->setConditionType('gteq')
            ->setValue(15)
            ->create();
        $subtotalFilter = $this->filterBuilder->setField('subtotal')
            ->setConditionType('eq')
            ->setValue($cart->getSubtotal())
            ->create();

        $yesterdayDate = (new \DateTime($cart->getCreatedAt()))->sub(new \DateInterval('P1D'))->format('Y-m-d');
        $tomorrowDate = (new \DateTime($cart->getCreatedAt()))->add(new \DateInterval('P1D'))->format('Y-m-d');
        $minCreatedAtFilter = $this->filterBuilder->setField('created_at')
            ->setConditionType('gteq')
            ->setValue($yesterdayDate)
            ->create();
        $maxCreatedAtFilter = $this->filterBuilder->setField('created_at')
            ->setConditionType('lteq')
            ->setValue($tomorrowDate)
            ->create();

        $this->searchCriteriaBuilder->addFilters([$grandTotalFilter, $subtotalFilter]);
        $this->searchCriteriaBuilder->addFilters([$minCreatedAtFilter]);
        $this->searchCriteriaBuilder->addFilters([$maxCreatedAtFilter]);
        $this->searchCriteriaBuilder->addFilter('reserved_order_id', 'test01');
        /** @var SortOrder $sortOrder */
        $sortOrder = $this->sortOrderBuilder->setField('subtotal')->setDirection(SortOrder::SORT_ASC)->create();
        $this->searchCriteriaBuilder->setSortOrders([$sortOrder]);
        $searchCriteria = $this->searchCriteriaBuilder->create()->__toArray();
        $requestData = ['searchCriteria' => $searchCriteria];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/search' . '?' . http_build_query($requestData),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'quoteCartRepositoryV1',
                'serviceVersion' => 'V1',
                'operation' => 'quoteCartRepositoryV1GetList',
            ],
        ];

        $searchResult = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertArrayHasKey('total_count', $searchResult);
        $this->assertEquals(1, $searchResult['total_count']);
        $this->assertArrayHasKey('items', $searchResult);
        $this->assertCount(1, $searchResult['items']);

        $cartData = $searchResult['items'][0];
        $this->assertEquals($cart->getId(), $cartData['id']);
        $this->assertEquals($cart->getCreatedAt(), $cartData['created_at']);
        $this->assertEquals($cart->getUpdatedAt(), $cartData['updated_at']);
        $this->assertEquals($cart->getIsActive(), $cartData['is_active']);

        $this->assertArrayHasKey('customer_is_guest', $cartData);
        $this->assertEquals(1, $cartData['customer_is_guest']);
    }

    /**
     */
    public function testGetListThrowsExceptionIfProvidedSearchFieldIsInvalid()
    {
        $this->expectException(\Exception::class);

        $serviceInfo = [
            'soap' => [
                'service' => 'quoteCartRepositoryV1',
                'serviceVersion' => 'V1',
                'operation' => 'quoteCartRepositoryV1GetList',
            ],
            'rest' => [
                'resourcePath' => '/V1/carts/search',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
        ];

        $invalidFilter = $this->filterBuilder->setField('invalid_field')
            ->setConditionType('eq')
            ->setValue(0)
            ->create();

        $this->searchCriteriaBuilder->addFilters([$invalidFilter]);
        $searchCriteria = $this->searchCriteriaBuilder->create()->__toArray();
        $requestData = ['searchCriteria' => $searchCriteria];
        $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * Saving quote - negative case, attempt to change customer id in the active quote for the user with Customer role.
     *
     * @dataProvider customerIdDataProvider
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_shipping_method.php
     */
    public function testSaveQuoteException($customerId)
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid state change requested');

        $token = $this->getToken();

        /** @var Quote $quote */
        $quote = $this->getCart('test_order_1');

        $requestData = $this->getRequestData($quote->getId());
        // Replace to customer id not much with current user id..
        $requestData['quote']['customer']['id'] = $customerId;

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::$mineCartUrl,
                'httpMethod'   => Request::HTTP_METHOD_PUT,
                'token'        => $token
            ],
            'soap' => [
                'service' => 'quoteCartRepositoryV1',
                'serviceVersion' => 'V1',
                'operation' => 'quoteCartRepositoryV1Save',
                'token' => $token
            ]
        ];

        $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * Saving quote - positive case: successful change correct customer data.
     *
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_shipping_method.php
     */
    public function testSaveQuote()
    {
        $token = $this->getToken();

        /** @var Quote $quote */
        $quote = $this->getCart('test_order_1');

        $requestData = $this->getRequestData($quote->getId());

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::$mineCartUrl,
                'httpMethod'   => Request::HTTP_METHOD_PUT,
                'token'        => $token
            ],
            'soap' => [
                'service'        => 'quoteCartRepositoryV1',
                'serviceVersion' => 'V1',
                'operation'      => 'quoteCartRepositoryV1Save',
                'token'          => $token
            ]
        ];

        $this->_webApiCall($serviceInfo, $requestData);

        $quote->loadActive($requestData["quote"]["id"]);
        $this->assertEquals($requestData["quote"]["customer"]["firstname"], $quote->getCustomerFirstname());
        $this->assertEquals($requestData["quote"]["customer"]["middlename"], $quote->getCustomerMiddlename());
        $this->assertEquals($requestData["quote"]["customer"]["lastname"], $quote->getCustomerLastname());
        $this->assertEquals($requestData["quote"]["customer"]["email"], $quote->getCustomerEmail());
    }

    /**
     * Request to api for the current user token.
     *
     * @return string
     */
    private function getToken()
    {
        $customerTokenService = $this->objectManager->create(
            CustomerTokenServiceInterface::class
        );

        return $customerTokenService->createCustomerAccessToken('customer@example.com', 'password');
    }

    /**
     * Request's data for tests.
     *
     * @param $quoteId Int
     * @return array
     */
    private function getRequestData($quoteId)
    {
        $requestData['quote'] = [
            'id'       => $quoteId,
            'store_id' => 1,
            'customer' => [
                'id'         => 1,
                'middlename' => 'Middlename_Test',
                'firstname'  => 'Firstname_Test',
                'lastname'   => 'Lastname_Test',
                'email'      => 'customer@test.com'
            ]
        ];

        return $requestData;
    }

    /**
     * Provides different types of customer id.
     *
     * @return array
     */
    public function customerIdDataProvider()
    {
        return [[999],[null],['25']];
    }
}
