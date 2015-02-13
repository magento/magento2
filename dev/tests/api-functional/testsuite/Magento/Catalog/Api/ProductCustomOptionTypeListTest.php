<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

class ProductCustomOptionTypeListTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/products/options/';

    const SERVICE_NAME = 'catalogProductCustomOptionTypeListV1';

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetTypes()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "types",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => 'V1',
                'operation' => self::SERVICE_NAME . 'GetItems',
            ],
        ];
        $types = $this->_webApiCall($serviceInfo);
        $excepted = [
            'label' => __('Drop-down'),
            'code' => 'drop_down',
            'group' => __('Select'),
        ];
        $this->assertGreaterThanOrEqual(10, count($types));
        $this->assertContains($excepted, $types);
    }
}
