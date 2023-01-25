<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Api;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Tests web-api for multishipping quote.
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

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->filterBuilder = $this->objectManager->create(FilterBuilder::class);
        $this->sortOrderBuilder = $this->objectManager->create(SortOrderBuilder::class);
        $this->searchCriteriaBuilder = $this->objectManager->create(SearchCriteriaBuilder::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        try {
            /** @var CartRepositoryInterface $quoteRepository */
            $quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
            $cart = $this->getCart('multishipping_quote_id');
            $quoteRepository->delete($cart);
        } catch (\InvalidArgumentException $e) {
            // Do nothing if cart fixture was not used
        }
        parent::tearDown();
    }

    /**
     * Tests that multishipping quote contains all addresses in shipping assignments.
     *
     * @magentoApiDataFixture Magento/Multishipping/Fixtures/quote_with_split_items.php
     */
    public function testGetMultishippingCart()
    {
        $cart = $this->getCart('multishipping_quote_id');
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

        $shippingAssignments = $cart->getExtensionAttributes()->getShippingAssignments();
        foreach ($shippingAssignments as $key => $shippingAssignment) {
            $address = $shippingAssignment->getShipping()->getAddress();
            $cartItem = $shippingAssignment->getItems()[0];
            $this->assertEquals(
                $address->getId(),
                $cartData['extension_attributes']['shipping_assignments'][$key]['shipping']['address']['id']
            );
            $this->assertEquals(
                $cartItem->getSku(),
                $cartData['extension_attributes']['shipping_assignments'][$key]['items'][0]['sku']
            );
            $this->assertEquals(
                $cartItem->getQty(),
                $cartData['extension_attributes']['shipping_assignments'][$key]['items'][0]['qty']
            );
        }
    }

    /**
     * Retrieve quote by given reserved order ID
     *
     * @param string $reservedOrderId
     * @return Quote
     * @throws \InvalidArgumentException
     */
    private function getCart(string $reservedOrderId): Quote
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
}
