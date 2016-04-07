<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

class ProductTypeListTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductTypeListV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products/';

    public function testGetProductTypes()
    {
        $expectedProductTypes = [
            [
                'name' => 'simple',
                'label' => 'Simple Product',
            ],
            [
                'name' => 'virtual',
                'label' => 'Virtual Product',
            ],
            [
                'name' => 'downloadable',
                'label' => 'Downloadable Product',
            ],
            [
                'name' => 'bundle',
                'label' => 'Bundle Product',
            ],
            [
                'name' => 'configurable',
                'label' => 'Configurable Product',
            ],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/types',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetProductTypes',
            ],
        ];

        $productTypes = $this->_webApiCall($serviceInfo);

        foreach ($expectedProductTypes as $expectedProductType) {
            $this->assertContains($expectedProductType, $productTypes);
        }
    }
}
