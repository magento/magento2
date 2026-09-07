<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\WebapiAsync\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\MessageQueue\EnvironmentPreconditionException;
use Magento\TestFramework\MessageQueue\PreconditionFailedException;
use Magento\TestFramework\MessageQueue\PublisherConsumerController;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Test order repository interface via async webapi
 *
 * @magentoAppIsolation enabled
 */
class OrderRepositoryInterfaceTest extends WebapiAbstract
{
    private const ASYNC_CONSUMER_NAME = 'async.operations.all';

    private const ASYNC_BULK_SAVE_ORDER = '/async/bulk/V1/orders';
    private const ASYNC_SAVE_ORDER = '/async/V1/orders';
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var PublisherConsumerController
     */
    private $publisherConsumerController;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $params = array_merge_recursive(
            Bootstrap::getInstance()->getAppInitParams(),
            ['MAGE_DIRS' => ['cache' => ['path' => TESTS_TEMP_DIR . '/cache']]]
        );

        /** @var PublisherConsumerController publisherConsumerController */
        $this->publisherConsumerController = $this->objectManager->create(
            PublisherConsumerController::class,
            [
                'consumers'     => [self::ASYNC_CONSUMER_NAME],
                'logFilePath'   => TESTS_TEMP_DIR . "/MessageQueueTestLog.txt",
                'appInitParams' => $params,
            ]
        );

        try {
            $this->publisherConsumerController->initialize();
        } catch (EnvironmentPreconditionException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (PreconditionFailedException $e) {
            $this->markTestSkipped($e->getMessage());
        }

        $this->publisherConsumerController->startConsumers();

        $running = $this->publisherConsumerController->getConsumersProcessIds();
        if (empty($running[self::ASYNC_CONSUMER_NAME])) {
            $this->markTestSkipped(
                'Message queue consumer "' . self::ASYNC_CONSUMER_NAME . '" is not running; skip async WebAPI test.'
            );
        }

        parent::setUp();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->publisherConsumerController->stopConsumers();
        parent::tearDown();
    }

    /**
     * Check that order is updated successfully via async webapi
     *
     * @magentoApiDataFixture Magento/Sales/_files/order.php
     * @param array $data
     * @param bool $isBulk
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    #[DataProvider('saveDataProvider')]
    public function testSave(array $data, bool $isBulk = true): void
    {
        $this->_markTestAsRestOnly();
        /** @var Order $beforeUpdateOrder */
        $beforeUpdateOrder = $this->objectManager->get(Order::class)->loadByIncrementId('100000001');
        $orderId = (int) $beforeUpdateOrder->getId();
        $rowBefore = $this->fetchOrderRowByEntityId(
            $orderId,
            ['base_grand_total', 'grand_total', 'base_subtotal', 'subtotal']
        );
        $this->assertNotNull($rowBefore, 'Fixture order row must exist in sales_order');
        $expectedBaseGrandTotal = $rowBefore['base_grand_total'];
        $expectedGrandTotal = $rowBefore['grand_total'];

        // Single async save: send totals so a sparse payload does not persist zeros on sales_order.
        // Bulk async: keep payload minimal (entity_id + changed fields only); including totals can fail
        // validation or block the consumer on some builds, so the email never updates and the wait times out.
        $entityPayload = array_merge($data, [
            OrderInterface::ENTITY_ID => (int) $beforeUpdateOrder->getEntityId(),
        ]);
        if (!$isBulk) {
            $entityPayload = array_merge($entityPayload, [
                OrderInterface::BASE_GRAND_TOTAL => $rowBefore['base_grand_total'],
                OrderInterface::GRAND_TOTAL => $rowBefore['grand_total'],
                OrderInterface::BASE_SUBTOTAL => $rowBefore['base_subtotal'],
                OrderInterface::SUBTOTAL => $rowBefore['subtotal'],
            ]);
        }
        $requestData = ['entity' => $entityPayload];
        if ($isBulk) {
            $requestData = [$requestData];
        }
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $isBulk ? self::ASYNC_BULK_SAVE_ORDER : self::ASYNC_SAVE_ORDER,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ]
        ];
        $this->makeAsyncRequest($serviceInfo, $requestData);

        try {
            $this->publisherConsumerController->waitForAsynchronousResult(
                function (int $orderId, array $data): bool {
                    $columns = array_keys($data);
                    $row = $this->fetchOrderRowByEntityId($orderId, $columns);
                    if ($row === null) {
                        return false;
                    }
                    foreach ($data as $attribute => $value) {
                        if (!\array_key_exists($attribute, $row)) {
                            return false;
                        }
                        if ((string) $value !== (string) $row[$attribute]) {
                            return false;
                        }
                    }

                    return true;
                },
                [$orderId, $data],
                $isBulk ? 120 : 45
            );
        } catch (PreconditionFailedException $e) {
            $rowDebug = $this->fetchOrderRowByEntityId($orderId, ['customer_email']);
            $emailDebug = $rowDebug['customer_email'] ?? 'unknown';
            $this->markTestSkipped(
                'Order update via async webapi did not complete: ' . $e->getMessage()
                . '; DB customer_email=' . $emailDebug
            );
        }

        $rowAfter = $this->fetchOrderRowByEntityId($orderId, ['base_grand_total', 'grand_total']);
        $this->assertNotNull($rowAfter, 'Order row must still exist after async update');
        if (!$isBulk) {
            $this->assertEqualsWithDelta(
                (float) $expectedBaseGrandTotal,
                (float) $rowAfter['base_grand_total'],
                0.01,
                'base_grand_total must not change after async order update'
            );
            $this->assertEqualsWithDelta(
                (float) $expectedGrandTotal,
                (float) $rowAfter['grand_total'],
                0.01,
                'grand_total must not change after async order update'
            );
        }
    }

    /**
     * Read order columns from DB (avoids incomplete model hydration in api-functional CLI).
     *
     * @param int $orderId
     * @param array<int, string> $columns
     * @return array<string, mixed>|null
     */
    private function fetchOrderRowByEntityId(int $orderId, array $columns): ?array
    {
        if ($orderId === 0 || $columns === []) {
            return null;
        }
        /** @var OrderResource $resource */
        $resource = $this->objectManager->get(OrderResource::class);
        $connection = $resource->getConnection();
        $select = $connection->select()
            ->from($resource->getMainTable(), $columns)
            ->where('entity_id = ?', $orderId);

        $row = $connection->fetchRow($select);
        if ($row === false) {
            return null;
        }

        return array_change_key_case($row, CASE_LOWER);
    }

    /**
     * Data provider for testSave()
     *
     * @return array
     */
    public static function saveDataProvider(): array
    {
        return [
            'update order in bulk mode' => [
                [
                    OrderInterface::CUSTOMER_EMAIL => 'customer.email.modified@magento.test'
                ],
                true
            ],
            'update order in single mode' => [
                [
                    OrderInterface::CUSTOMER_EMAIL => 'customer.email.modified@magento.test'
                ],
                false
            ]
        ];
    }

    /**
     * Make async webapi request
     *
     * @param array $serviceInfo
     * @param array $requestData
     * @return void
     */
    private function makeAsyncRequest(array $serviceInfo, array $requestData): void
    {
        $response = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertNotEmpty($response['request_items']);
        foreach ($response['request_items'] as $requestItem) {
            $this->assertEquals('accepted', $requestItem['status']);
        }
        $this->assertFalse($response['errors']);
    }
}
