<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\ConfigurableProduct;

use Exception;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Class for Store Config Configurable Product Image settings
 */
class StoreConfigTest extends GraphQlAbstract
{
    /**
     * Check type of configurable_thumbnail_source storeConfig configurable_thumbnail_source
     *
     * @magentoConfigFixture default_store checkout/cart/configurable_product_image itself
     *
     * @throws Exception
     */
    public function testReturnTypeAutocompleteOnStorefrontConfig()
    {
        $query = <<<QUERY
{
    storeConfig {
        configurable_thumbnail_source
    }
}
QUERY;
        $response = $this->graphQlQuery($query);
        self::assertArrayHasKey('configurable_thumbnail_source', $response['storeConfig']);
        self::assertEquals('itself', $response['storeConfig']['configurable_thumbnail_source']);
    }
}
