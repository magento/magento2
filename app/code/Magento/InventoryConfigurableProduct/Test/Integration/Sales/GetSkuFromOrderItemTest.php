<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Test\Integration\Sales;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventorySalesApi\Model\GetSkuFromOrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Class GetSkuFromOrderItemTest
 */
class GetSkuFromOrderItemTest extends TestCase
{
    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    /**
     * @var GetSkuFromOrderItemInterface
     */
    private $getSkuFromOrderItemInterface;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->orderItemRepository = Bootstrap::getObjectManager()->get(OrderItemRepositoryInterface::class);
        $this->getSkuFromOrderItemInterface = Bootstrap::getObjectManager()->get(GetSkuFromOrderItemInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/order_item_with_configurable_and_options.php
     */
    public function testGetSkuFromConfigurableProductWithCustomOptionsOrderItem()
    {
        $orderItems = $this->orderItemRepository->getList($this->searchCriteriaBuilder->create())
        ->getItems();
        $sku = $this->getSkuFromOrderItemInterface->execute(current($orderItems));
        $this->assertEquals('configurable', $sku);
    }
}
