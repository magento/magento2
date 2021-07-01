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
        default_display_currency_code
        default_display_currency_symbol
        available_currency_codes
        available_currencies {
            code
            value
    		name
    		symbol
        }
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
        $this->assertArrayHasKey('default_display_currency_code', $result['currency']);
        $this->assertArrayHasKey('default_display_currency_symbol', $result['currency']);
        $this->assertArrayHasKey('available_currency_codes', $result['currency']);
        $this->assertArrayHasKey('exchange_rates', $result['currency']);
        $this->assertArrayHasKey('available_currencies', $result['currency']);
        $this->assertNotEmpty($result['currency']['available_currencies']);
        $available_currency = $result['currency']['available_currencies'][0];
        $this->assertEquals('AUD', $available_currency['code']);
        $this->assertEquals('Australian Dollar', $available_currency['name']);
        $this->assertEquals('A$', $available_currency['symbol']);
        $this->assertEquals('0', $available_currency['value']);
    }
}
