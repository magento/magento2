<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
    ],
    'in_progress_success' => [
        'uuid' => 'bulk-uuid-2',
        'user_id' => 1,
        'description' => 'Bulk Description',
    ],
    'in_progress_failed' => [
        'uuid' => 'bulk-uuid-3',
        'user_id' => 1,
        'description' => 'Bulk Description',
    ],
    'finish_success' => [
        'uuid' => 'bulk-uuid-4',
        'user_id' => 1,
        'description' => 'Bulk Description',
    ],
    'finish_failed' => [
        'uuid' => 'bulk-uuid-5',
        'user_id' => 1,
        'description' => 'Bulk Description',
    ]
];

$operations = [
    [
        'bulk_uuid' => 'bulk-uuid-1',
        'topic_name' => 'topic-1',
        'serialized_data' => json_encode(['entity_id' => 1]),
        'status' => OperationInterface::STATUS_TYPE_OPEN,
        'error_code' => null,
        'result_message' => null,
    ],
    [
        'bulk_uuid' => 'bulk-uuid-2',
        'topic_name' => 'topic-2',
        'serialized_data' => json_encode(['entity_id' => 2]),
        'status' => OperationInterface::STATUS_TYPE_OPEN,
        'error_code' => null,
        'result_message' => null,
    ],
    [
        'bulk_uuid' => 'bulk-uuid-2',
        'topic_name' => 'topic-3',
        'serialized_data' => json_encode(['entity_id' => 2]),
        'status' => OperationInterface::STATUS_TYPE_OPEN,
        'error_code' => null,
        'result_message' => null,
    ],
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
        'status' => OperationInterface::STATUS_TYPE_OPEN,
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

];

$bulkQuery = "INSERT INTO {$bulkTable} (`uuid`, `user_id`, `description`) VALUES (:uuid, :user_id, :description);";
foreach ($bulks as $bulk) {
    $connection->query($bulkQuery, $bulk);
}

$operationQuery = "INSERT INTO {$operationTable}"
    . " (`bulk_uuid`, `topic_name`, `serialized_data`, `status`, `error_code`, `result_message`)"
    . " VALUES (:bulk_uuid, :topic_name, :serialized_data, :status, :error_code, :result_message);";
foreach ($operations as $operation) {
    $connection->query($operationQuery, $operation);
}
