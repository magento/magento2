<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Exception;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Class for Store Config customers settings
 */
class StoreConfigTest extends GraphQlAbstract
{
    /**
     * Check type of autocomplete_on_storefront storeConfig value
     *
     * @magentoConfigFixture default_store customer/password/autocomplete_on_storefront 1
     *
     * @throws Exception
     */
    public function testReturnTypeAutocompleteOnStorefrontConfig()
    {
        $query = <<<QUERY
{
    storeConfig {
        autocomplete_on_storefront
    }
}
QUERY;
        $response = $this->graphQlQuery($query);
        self::assertArrayHasKey('autocomplete_on_storefront', $response['storeConfig']);
        self::assertTrue($response['storeConfig']['autocomplete_on_storefront']);
    }
}
