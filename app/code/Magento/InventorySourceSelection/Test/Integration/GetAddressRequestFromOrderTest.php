<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelection\Test\Integration;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventorySourceSelection\Model\GetAddressRequestFromOrder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class GetAddressRequestFromOrderTest extends TestCase
{
    /**
     * @var GetAddressRequestFromOrder
     */
    private $getAddressRequestFromOrder;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->getAddressRequestFromOrder = Bootstrap::getObjectManager()->get(GetAddressRequestFromOrder::class);
        $this->orderRepository = Bootstrap::getObjectManager()->get(OrderRepositoryInterface::class);
    }

    /**
     * @param string $incrementId
     * @return OrderInterface
     */
    private function getOrderByIncrementId(string $incrementId): OrderInterface
    {
        /** @var OrderRepositoryInterface $orderRepository */
        $orderRepository = Bootstrap::getObjectManager()->get(OrderRepositoryInterface::class);

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder
            ->addFilter('increment_id', $incrementId)
            ->create();

        return current($orderRepository->getList($searchCriteria)->getItems());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     *
     * @return void
     */
    public function testGetDistanceProviderCode()
    {
        $order = $this->getOrderByIncrementId('100000001');
        $addressRequest = $this->getAddressRequestFromOrder->execute((int) $order->getEntityId());

        self::assertEquals('11111', $addressRequest->getPostcode());
        self::assertEquals('Los Angeles', $addressRequest->getCity());
        self::assertEquals('US', $addressRequest->getCountry());
        self::assertEquals('CA', $addressRequest->getRegion());
        self::assertEquals('street', $addressRequest->getStreetAddress());
    }
}
