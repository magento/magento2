<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WebapiAsync\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\MessageQueue\EnvironmentPreconditionException;
use Magento\TestFramework\MessageQueue\PreconditionFailedException;
use Magento\TestFramework\MessageQueue\PublisherConsumerController;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Test order repository interface via async webapi
 */
class OrderRepositoryInterfaceTest extends WebapiAbstract
{
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
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();

        $params = array_merge_recursive(
            Bootstrap::getInstance()->getAppInitParams(),
            ['MAGE_DIRS' => ['cache' => ['path' => TESTS_TEMP_DIR . '/cache']]]
        );

        /** @var PublisherConsumerController publisherConsumerController */
        $this->publisherConsumerController = $this->objectManager->create(
            PublisherConsumerController::class,
            [
                'consumers'     => ['async.operations.all'],
                'logFilePath'   => TESTS_TEMP_DIR . "/MessageQueueTestLog.txt",
                'appInitParams' => $params,
            ]
        );

        try {
            $this->publisherConsumerController->initialize();
        } catch (EnvironmentPreconditionException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (PreconditionFailedException $e) {
            $this->fail(
                $e->getMessage()
            );
        }
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
     * Check that order is updated successfuly via async webapi
     *
     * @magentoApiDataFixture Magento/Sales/_files/order.php
     * @dataProvider saveDataProvider
     * @param array $data
     * @param bool $isBulk
     * @return void
     */
    public function testSave(array $data, bool $isBulk = true): void
    {
        $this->_markTestAsRestOnly();
        /** @var Order $beforeUpdateOrder */
        $beforeUpdateOrder = $this->objectManager->get(Order::class)->loadByIncrementId('100000001');
        $requestData = [
            'entity' => array_merge($data, [OrderInterface::ENTITY_ID => $beforeUpdateOrder->getEntityId()])
        ];
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
                function (Order $beforeUpdateOrder, array $data) {
                    /** @var Order $afterUpdateOrder */
                    $afterUpdateOrder = $this->objectManager->get(Order::class)->load($beforeUpdateOrder->getId());
                    foreach ($data as $attribute => $value) {
                        $getter = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $attribute)));
                        if ($value !== $afterUpdateOrder->$getter()) {
                            return false;
                        }
                    }
                    //check that base_grand_total and grand_total are not overwritten
                    $this->assertEquals(
                        $beforeUpdateOrder->getBaseGrandTotal(),
                        $afterUpdateOrder->getBaseGrandTotal()
                    );
                    $this->assertEquals(
                        $beforeUpdateOrder->getGrandTotal(),
                        $afterUpdateOrder->getGrandTotal()
                    );
                    return true;
                },
                [$beforeUpdateOrder, $data]
            );
        } catch (PreconditionFailedException $e) {
            $this->fail("Order update via async webapi failed");
        }
    }

    /**
     * Data provider for tesSave()
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
