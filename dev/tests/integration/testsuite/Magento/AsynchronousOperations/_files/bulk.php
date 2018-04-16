<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Bulk\OperationInterface;

/**
 * @var $resource Magento\Framework\App\ResourceConnection
 */
$resource = Bootstrap::getObjectManager()->get(\Magento\Framework\App\ResourceConnection::class);
$connection = $resource->getConnection();
$bulkTable = $resource->getTableName('magento_bulk');
$operationTable = $resource->getTableName('magento_operation');

$bulks = [
    'not_started' => [
        'uuid' => 'bulk-uuid-1',
        'user_id' => 1,
        'description' => 'Bulk Description',
        'operation_count' => 1,
        'start_time' => null,
    ],
    'in_progress_success' => [
        'uuid' => 'bulk-uuid-2',
        'user_id' => 1,
        'description' => 'Bulk Description',
        'operation_count' => 3,
        'start_time' => null,
    ],
    'in_progress_failed' => [
        'uuid' => 'bulk-uuid-3',
        'user_id' => 1,
        'description' => 'Bulk Description',
        'operation_count' => 2,
        'start_time' => null,
    ],
    'finish_success' => [
        'uuid' => 'bulk-uuid-4',
        'user_id' => 1,
        'description' => 'Bulk Description',
        'operation_count' => 1,
        'start_time' => null,
    ],
    'finish_failed' => [
        'uuid' => 'bulk-uuid-5',
        'user_id' => 1,
        'description' => 'Bulk Description',
        'operation_count' => 2,
        'start_time' => null,
    ],
    'started_searchable' => [
        'uuid' => 'bulk-uuid-6',
        'user_id' => 1,
        'description' => 'Bulk Description',
        'operation_count' => 3,
        'start_time' => '2009-10-10 00:00:00'
    ]
];
// Only processed operations are saved into database (i.e. operations that are not in 'open' state)
$operations = [
    [
        'bulk_uuid' => 'bulk-uuid-2',
        'topic_name' => 'topic-3',
        'serialized_data' => json_encode(['entity_id' => 2]),
        'status' => OperationInterface::STATUS_TYPE_COMPLETE,
        'error_code' => null,
        'result_message' => null,
    ],
    [
        'bulk_uuid' => 'bulk-uuid-3',
        'topic_name' => 'topic-3',
        'serialized_data' => json_encode(['entity_id' => 3]),
        'status' => OperationInterface::STATUS_TYPE_RETRIABLY_FAILED,
        'error_code' => 1111,
        'result_message' => 'Something went wrong during your request',
    ],
    [
        'bulk_uuid' => 'bulk-uuid-4',
        'topic_name' => 'topic-4',
        'serialized_data' => json_encode(['entity_id' => 4]),
        'status' => OperationInterface::STATUS_TYPE_COMPLETE,
        'error_code' => null,
        'result_message' => null,
    ],
    [
        'bulk_uuid' => 'bulk-uuid-5',
        'topic_name' => 'topic-4',
        'serialized_data' => json_encode(['entity_id' => 5, 'meta_information' => 'Test']),
        'status' => OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED,
        'error_code' => 1111,
        'result_message' => 'Something went wrong during your request',
    ],
    [
        'bulk_uuid' => 'bulk-uuid-5',
        'topic_name' => 'topic-4',
        'serialized_data' => json_encode(['entity_id' => 5]),
        'status' => OperationInterface::STATUS_TYPE_RETRIABLY_FAILED,
        'error_code' => 2222,
        'result_message' => 'Entity with ID=4 does not exist',
    ],
    [
        'bulk_uuid' => 'bulk-uuid-6',
        'topic_name' => 'topic-5',
        'serialized_data' => json_encode(['entity_id' => 5]),
        'status' => OperationInterface::STATUS_TYPE_COMPLETE,
        'error_code' => null,
        'result_message' => null,
    ],
    [
        'bulk_uuid' => 'bulk-uuid-6',
        'topic_name' => 'topic-5',
        'serialized_data' => json_encode(['entity_id' => 5, 'meta_information' => 'Test']),
        'status' => OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED,
        'error_code' => 1111,
        'result_message' => 'Something went wrong during your request',
    ],
    [
        'bulk_uuid' => 'bulk-uuid-6',
        'topic_name' => 'topic-5',
        'serialized_data' => json_encode(['entity_id' => 5]),
        'status' => OperationInterface::STATUS_TYPE_RETRIABLY_FAILED,
        'error_code' => 2222,
        'result_message' => 'Entity with ID=4 does not exist',
    ],

];

$bulkQuery = "INSERT INTO {$bulkTable} (`uuid`, `user_id`, `description`, `operation_count`, `start_time`)"
    . " VALUES (:uuid, :user_id, :description, :operation_count, :start_time);";
foreach ($bulks as $bulk) {
    $connection->query($bulkQuery, $bulk);
}

$operationQuery = "INSERT INTO {$operationTable}"
    . " (`bulk_uuid`, `topic_name`, `serialized_data`, `status`, `error_code`, `result_message`)"
    . " VALUES (:bulk_uuid, :topic_name, :serialized_data, :status, :error_code, :result_message);";
foreach ($operations as $operation) {
    $connection->query($operationQuery, $operation);
}
