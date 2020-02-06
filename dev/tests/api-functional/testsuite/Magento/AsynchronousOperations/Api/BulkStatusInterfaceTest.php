<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Bulk\OperationInterface;

class BulkStatusInterfaceTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/bulk/';
    const SERVICE_NAME = 'asynchronousOperationsBulkStatusV1';
    const GET_COUNT_OPERATION_NAME = "GetOperationsCountByBulkIdAndStatus";
    const TEST_UUID = "bulk-uuid-searchable-6";

    /**
     * @magentoApiDataFixture Magento/AsynchronousOperations/_files/operation_searchable.php
     */
    public function testGetListByBulkStartTime()
    {
        $resourcePath = self::RESOURCE_PATH
            . self::TEST_UUID
            . "/operation-status/"
            . OperationInterface::STATUS_TYPE_OPEN;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $resourcePath,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => 'V1',
                'operation' => self::SERVICE_NAME . self::GET_COUNT_OPERATION_NAME
            ],
        ];
        $qty = $this->_webApiCall(
            $serviceInfo,
            ['bulkUuid' => self::TEST_UUID, 'status' => OperationInterface::STATUS_TYPE_OPEN]
        );
        $this->assertEquals(2, $qty);
    }
}
