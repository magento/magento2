<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Directory;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test the GraphQL endpoint's Currency query
 */
class CurrencyTest extends GraphQlAbstract
{
    public function testGetCurrency()
    {
        $query = <<<QUERY
query {
    currency {
        base_currency_code
        base_currency_symbol
        default_display_currecy_code
        default_display_currecy_symbol
        available_currency_codes
        exchange_rates {
            currency_to
            rate
        }
    }
}
QUERY;

        $result = $this->graphQlQuery($query);
        $this->assertArrayHasKey('currency', $result);
        $this->assertArrayHasKey('base_currency_code', $result['currency']);
        $this->assertArrayHasKey('base_currency_symbol', $result['currency']);
        $this->assertArrayHasKey('default_display_currecy_code', $result['currency']);
        $this->assertArrayHasKey('default_display_currecy_symbol', $result['currency']);
        $this->assertArrayHasKey('available_currency_codes', $result['currency']);
        $this->assertArrayHasKey('exchange_rates', $result['currency']);
    }
}
