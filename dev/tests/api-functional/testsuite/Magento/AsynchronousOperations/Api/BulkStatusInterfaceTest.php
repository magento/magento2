<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Api;

use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Bulk\OperationInterface;

class BulkStatusInterfaceTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/bulk/';
    const SERVICE_NAME = 'asynchronousOperationsBulkStatusV1';
    const GET_COUNT_OPERATION_NAME = "getOperationsCountByBulkIdAndStatus";

    /**
     * @magentoApiDataFixture Magento/AsynchronousOperations/_files/operation_searchable.php
     * @dataProvider getBulkOperationCountDataProvider
     * @param string $bulkUuid
     * @param int $expectedOperationCount
     * @param int $status
     * @return void
     */
    public function testGetOperationsCountByBulkIdAndStatus(
        string $bulkUuid,
        int $expectedOperationCount,
        int $status
    ): void {
        $resourcePath = self::RESOURCE_PATH . $bulkUuid . '/operation-status/' . $status;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $resourcePath,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => 'V1',
                'operation' => self::SERVICE_NAME . self::GET_COUNT_OPERATION_NAME,
            ],
        ];
        $actualOperationCount = $this->_webApiCall(
            $serviceInfo,
            ['bulkUuid' => $bulkUuid, 'status' => $status]
        );
        $this->assertEquals($expectedOperationCount, $actualOperationCount);
    }

    /**
     * @return array
     */
    public static function getBulkOperationCountDataProvider(): array
    {
        return [
            'Completed operations' => [
                'bulk-uuid-searchable-6',
                1,
                OperationInterface::STATUS_TYPE_COMPLETE,
            ],
            'Failed operations, can try to perform again' => [
                'bulk-uuid-searchable-6',
                1,
                OperationInterface::STATUS_TYPE_RETRIABLY_FAILED,
            ],
            'Failed operations. Must change something to retry' => [
                'bulk-uuid-searchable-6',
                1,
                OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED,
            ],
            'Opened operations' => [
                'bulk-uuid-searchable-6',
                2,
                OperationInterface::STATUS_TYPE_OPEN,
            ],
            'Rejected operations' => [
                'bulk-uuid-searchable-6',
                1,
                OperationInterface::STATUS_TYPE_REJECTED,
            ],
            'Not started scheduled operations by open status' => [
                'bulk-uuid-searchable-7',
                0,
                OperationInterface::STATUS_TYPE_OPEN,
            ],
        ];
    }
}
