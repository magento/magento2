<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Ui\Component\Listing\Column;

use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for \Magento\Sales\Ui\Component\Listing\Column\PurchasedPrice
 *
 * @magentoAppArea adminhtml
 */
class PurchasedPriceTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var PurchasedPrice
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(PurchasedPrice::class, [
            'data' => [
                'name' => 'subtotal',
            ],
        ]);
    }

    /**
     * Verify prepare data source without order currency code
     *
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @return void
     */
    public function testPrepareDataSourceWithoutOrderCurrencyCode(): void
    {
        $order = $this->objectManager->get(Order::class)->loadByIncrementId('100000001');

        $dataSource = [
            'data' => [
                'items' => [
                    [
                        'order_id' => $order->getEntityId(),
                        'subtotal' => 100,
                    ],
                ],
            ],
        ];

        $result = $this->model->prepareDataSource($dataSource);
        $this->assertEquals('$100.00', $result['data']['items'][0]['subtotal']);
    }
}
