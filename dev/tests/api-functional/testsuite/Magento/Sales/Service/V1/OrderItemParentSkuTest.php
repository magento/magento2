<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Service\V1;

use Magento\Framework\Webapi\Rest\Request;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Checks the parent_sku extension attribute of order items in web API responses
 */
class OrderItemParentSkuTest extends WebapiAbstract
{
    private const SERVICE_VERSION = 'V1';

    private const ORDER_INCREMENT_ID = '100000001';

    /**
     * @magentoApiDataFixture Magento/Sales/_files/order_configurable_product.php
     */
    public function testOrderGetReturnsParentSkuOfConfigurableItem(): void
    {
        $order = $this->getOrder();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/orders/' . $order->getId(),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'salesOrderRepositoryV1',
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => 'salesOrderRepositoryV1get',
            ],
        ];

        $result = $this->_webApiCall($serviceInfo, ['id' => $order->getId()]);

        $this->assertParentSkus($result['items']);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/order_configurable_product.php
     */
    public function testOrderItemGetReturnsParentSkuOfConfigurableItem(): void
    {
        $parentItem = null;
        foreach ($this->getOrder()->getAllItems() as $item) {
            if (!$item->getParentItemId()) {
                $parentItem = $item;
            }
        }
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/orders/items/' . $parentItem->getItemId(),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'salesOrderItemRepositoryV1',
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => 'salesOrderItemRepositoryV1get',
            ],
        ];

        $result = $this->_webApiCall($serviceInfo, ['id' => $parentItem->getItemId()]);

        $this->assertSame('configurable', $result['extension_attributes']['parent_sku']);
    }

    /**
     * Assert that only the configurable item carries the configurable product's SKU as parent_sku
     *
     * @param array $items
     * @return void
     */
    private function assertParentSkus(array $items): void
    {
        $parentSkus = [];
        foreach ($items as $item) {
            $parentSkus[$item['product_type']] = $item['extension_attributes']['parent_sku'] ?? null;
        }
        ksort($parentSkus);

        $this->assertSame(['configurable' => 'configurable', 'simple' => null], $parentSkus);
    }

    /**
     * Load the fixture order
     *
     * @return Order
     */
    private function getOrder(): Order
    {
        return Bootstrap::getObjectManager()->create(Order::class)->loadByIncrementId(self::ORDER_INCREMENT_ID);
    }
}
