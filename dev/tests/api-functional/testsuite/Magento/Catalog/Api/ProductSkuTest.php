<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Api;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\TestCase\WebapiAbstract;

class ProductSkuTest extends WebapiAbstract
{
    private const RESOURCE_PATH = '/V1/products/';

    /**
     * @return void
     */
    #[
        DataFixture(ProductFixture::class, [
            'sku' => 'SKU:@&=$\,;1234',
            'name' => 'Test product 1'
        ])
    ]
    public function testGetProductDetailsWithSpecialCharsSKUAndQueryParams(): void
    {
        $this->_markTestAsRestOnly();

        $sku = 'SKU:@&=$\,;1234';
        $requestData = [
            'assetId' => 'urn:aaid:aeme47fc635-c87e-4a7e-8eb1-f74b4b77866c' . $sku
        ];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $sku . '?' . http_build_query($requestData),
                'httpMethod' => 'GET',
            ],
        ];
        $response = $this->_webApiCall($serviceInfo);
        self::assertArrayHasKey('id', $response);
        self::assertArrayHasKey('sku', $response);
        self::assertEquals($sku, $response['sku']);
    }
}
