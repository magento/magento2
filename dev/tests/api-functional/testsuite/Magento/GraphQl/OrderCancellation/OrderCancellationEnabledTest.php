<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\OrderCancellation;

use Magento\Store\Test\Fixture\Store;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test coverage for order cancellation settings in the store config
 */
#[
    DataFixture(Store::class)
]
class OrderCancellationEnabledTest extends GraphQlAbstract
{
    private const STORE_CONFIG_QUERY = <<<QUERY
{
    storeConfig {
        code
        order_cancellation_enabled
    }
}
QUERY;

    #[
        Config('sales/cancellation/enabled', 1)
    ]
    public function testOrderCancellationEnabledConfig()
    {
        $response = $this->graphQlQuery(self::STORE_CONFIG_QUERY);

        self::assertArrayHasKey('order_cancellation_enabled', $response['storeConfig']);
        self::assertEquals(true, $response['storeConfig']['order_cancellation_enabled']);
    }

    #[
        Config('sales/cancellation/enabled', 0)
    ]
    public function testOrderCancellationDisabledConfig()
    {
        $response = $this->graphQlQuery(self::STORE_CONFIG_QUERY);

        self::assertArrayHasKey('order_cancellation_enabled', $response['storeConfig']);
        self::assertEquals(false, $response['storeConfig']['order_cancellation_enabled']);
    }
}
