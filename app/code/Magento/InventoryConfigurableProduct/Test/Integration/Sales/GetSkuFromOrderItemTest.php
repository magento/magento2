<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Test\Integration\Sales;

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
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->orderItemRepository = Bootstrap::getObjectManager()->get(OrderItemRepositoryInterface::class);
        $this->getSkuFromOrderItemInterface = Bootstrap::getObjectManager()->get(GetSkuFromOrderItemInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/order_item_with_configurable_and_options.php
     */
    public function testGetSkuFromConfigurableProductWithCustomOptionsOrderItem()
    {
        $orderItem = $this->orderItemRepository->get(1);
        $sku = $this->getSkuFromOrderItemInterface->execute($orderItem);
        $this->assertEquals('configurable', $sku);
    }
}
