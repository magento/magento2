<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\WebapiAsync\Model;

use Magento\Framework\Bulk\OperationInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\MessageQueue\EnvironmentPreconditionException;
use Magento\TestFramework\MessageQueue\PreconditionFailedException;
use Magento\TestFramework\MessageQueue\PublisherConsumerController;
use Magento\TestFramework\TestCase\WebapiAbstract;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Verify that bulk operations with REST parameter aliases succeed in the consumer.
 *
 * The current REST pipeline canonicalizes names before publishing. Raw snake_case
 * messages are covered separately by MessageEncoderMethodParametersTest.
 *
 * @magentoAppIsolation enabled
 */
class AsyncBulkParameterNamesTest extends WebapiAbstract
{
    /**
     * @var PublisherConsumerController|null
     */
    private ?PublisherConsumerController $publisherConsumerController = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->_markTestAsRestOnly();

        $params = array_merge_recursive(
            Bootstrap::getInstance()->getAppInitParams(),
            ['MAGE_DIRS' => ['cache' => ['path' => TESTS_TEMP_DIR . '/cache']]]
        );
        $this->publisherConsumerController = Bootstrap::getObjectManager()->create(
            PublisherConsumerController::class,
            [
                'consumers' => ['async.operations.all'],
                'logFilePath' => TESTS_TEMP_DIR . '/MessageQueueParameterNamesTestLog.txt',
                'appInitParams' => $params,
            ]
        );

        try {
            $this->publisherConsumerController->initialize();
        } catch (EnvironmentPreconditionException $exception) {
            $this->markTestSkipped($exception->getMessage());
        }
    }

    /**
     * The existing test service echoes entityItem, allowing the consumer's result to be checked.
     */
    #[DataProvider('parameterNameProvider')]
    public function testBulkConsumerAcceptsParameterNames(string $parameterName): void
    {
        $items = [
            ['item_id' => 42, 'name' => 'First bulk item'],
            ['item_id' => 43, 'name' => 'Second bulk item'],
        ];
        $response = $this->_webApiCall(
            [
                'rest' => [
                    'resourcePath' => '/async/bulk/V1/testmoduleMSC/itemAnyType',
                    'httpMethod' => Request::HTTP_METHOD_POST,
                ],
            ],
            [[$parameterName => $items[0]], [$parameterName => $items[1]]]
        );

        self::assertNotEmpty($response['bulk_uuid']);
        self::assertFalse($response['errors']);
        self::assertCount(2, $response['request_items']);
        foreach ($response['request_items'] as $requestItem) {
            self::assertSame('accepted', $requestItem['status']);
        }

        $bulkStatus = [];
        try {
            $this->publisherConsumerController->waitForAsynchronousResult(
                function () use ($response, &$bulkStatus): bool {
                    $bulkStatus = $this->_webApiCall([
                        'rest' => [
                            'resourcePath' => '/V1/bulk/' . $response['bulk_uuid'] . '/detailed-status',
                            'httpMethod' => Request::HTTP_METHOD_GET,
                        ],
                    ]);

                    if (count($bulkStatus['operations_list']) !== 2) {
                        return false;
                    }
                    foreach ($bulkStatus['operations_list'] as $operation) {
                        if ((int)$operation['status'] === OperationInterface::STATUS_TYPE_OPEN) {
                            return false;
                        }
                    }

                    return true;
                }
            );
        } catch (PreconditionFailedException $exception) {
            self::fail(
                sprintf(
                    'Bulk "%s" did not finish: %s. Last status: %s',
                    $response['bulk_uuid'],
                    $exception->getMessage(),
                    json_encode($bulkStatus)
                )
            );
        }

        $actualItems = [];
        foreach ($bulkStatus['operations_list'] as $operation) {
            self::assertSame(
                OperationInterface::STATUS_TYPE_COMPLETE,
                (int)$operation['status'],
                $operation['result_message'] ?? 'Bulk consumer did not complete the operation.'
            );
            $result = json_decode($operation['result_serialized_data'], true, 512, JSON_THROW_ON_ERROR);
            $actualItems[(int)$operation['id']] = ['item_id' => $result['item_id'], 'name' => $result['name']];
        }
        ksort($actualItems);
        self::assertSame($items, $actualItems);
    }

    /**
     * @return array
     */
    public static function parameterNameProvider(): array
    {
        return [
            'snake_case' => ['entity_item'],
            'camelCase' => ['entityItem'],
        ];
    }

    protected function tearDown(): void
    {
        try {
            $this->publisherConsumerController?->stopConsumers();
        } finally {
            parent::tearDown();
        }
    }
}
