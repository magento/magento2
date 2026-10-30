<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Paypal;

use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test coverage for PayPal Express Checkout availability in the store config
 */
class StoreConfigTest extends GraphQlAbstract
{
    private const STORE_CONFIG_QUERY = <<<QUERY
{
    storeConfig {
        paypal_express_active
        paypal_express_visible_on_cart
    }
}
QUERY;

    #[Config('payment/paypal_express/active', 1)]
    #[Config('payment/paypal_express/visible_on_cart', 1)]
    public function testStoreConfigPaypalExpressEnabledAndVisible(): void
    {
        $response = $this->graphQlQuery(self::STORE_CONFIG_QUERY);

        self::assertTrue($response['storeConfig']['paypal_express_active']);
        self::assertTrue($response['storeConfig']['paypal_express_visible_on_cart']);
    }

    #[Config('payment/paypal_express/active', 0)]
    #[Config('payment/paypal_express/visible_on_cart', 0)]
    public function testStoreConfigPaypalExpressDisabledAndHidden(): void
    {
        $response = $this->graphQlQuery(self::STORE_CONFIG_QUERY);

        self::assertFalse($response['storeConfig']['paypal_express_active']);
        self::assertFalse($response['storeConfig']['paypal_express_visible_on_cart']);
    }
}
