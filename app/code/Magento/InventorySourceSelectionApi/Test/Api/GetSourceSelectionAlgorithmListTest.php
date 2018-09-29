<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Test\Api;

use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\TestCase\WebapiAbstract;

class GetSourceSelectionAlgorithmListTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/source-selection-algorithm-list';
    const SERVICE_NAME = 'inventorySourceSelectionApiGetSourceSelectionAlgorithmListV1';
    /**#@-*/

    public function testGetSourceSelectionAlgorithmList()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Execute',
            ],
        ];

        $sourceSelectionAlgorithmList = (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo);

        self::assertInternalType('array', $sourceSelectionAlgorithmList);
        self::assertNotEmpty($sourceSelectionAlgorithmList);
    }
}
