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

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartRepositoryTest extends WebapiAbstract
{
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

    protected function setUp()
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

    protected function tearDown()
    {
        try {
            $cart = $this->getCart('test01');
            $cart->delete();
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
    protected function getCart($reservedOrderId)
    {
        /** @var $cart \Magento\Quote\Model\Quote */
        $cart = $this->objectManager->get(\Magento\Quote\Model\Quote::class);
        $cart->load($reservedOrderId, 'reserved_order_id');
        if (!$cart->getId()) {
            throw new \InvalidArgumentException('There is no quote with provided reserved order ID.');
        }
        return $cart;
    }

    /**
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
        $this->assertContains('customer', $cartData);
        $this->assertEquals(true, $cartData['customer_is_guest']);
        $this->assertContains('currency', $cartData);
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
     * @expectedException \Exception
     * @expectedExceptionMessage No such entity with
     */
    public function testGetCartThrowsExceptionIfThereIsNoCartWithProvidedId()
    {
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

        $this->assertContains('customer_is_guest', $cartData);
        $this->assertEquals(1, $cartData['customer_is_guest']);
    }

    /**
     * @expectedException \Exception
     */
    public function testGetListThrowsExceptionIfProvidedSearchFieldIsInvalid()
    {
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
}
