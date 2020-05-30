<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Shipping\Controller\Adminhtml\Order\Shipment\Save;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\MessageInterface;
use Magento\Shipping\Controller\Adminhtml\Order\Shipment\AbstractShipmentControllerTest;
use Magento\Framework\Escaper;

/**
 * Test cases related to check that shipment creates as expected or proper error message appear.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 *
 * @see \Magento\Shipping\Controller\Adminhtml\Order\Shipment\Save::execute
 */
class CreateShipmentForOrderTest extends AbstractShipmentControllerTest
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->escaper = $this->_objectManager->get(Escaper::class);
    }

    /**
     * Assert that shipment created successfully.
     *
     * @magentoDataFixture Magento/Sales/_files/order_with_two_order_items_with_simple_product.php
     * @dataProvider dataForCreateShipmentDataProvider
     *
     * @param array $dataForBuildPostData
     * @param array $expectedShippedQtyBySku
     * @return void
     */
    public function testCreateOrderShipment(array $dataForBuildPostData, array $expectedShippedQtyBySku): void
    {
        $postData = $this->createPostData($dataForBuildPostData);
        $order = $this->orderRepository->get($postData['order_id']);
        $shipment = $this->getShipment($order);
        $this->assertNull($shipment->getEntityId());
        $this->performShipmentCreationRequest($postData);
        $this->assertCreateShipmentRequestSuccessfullyPerformed((int)$order->getEntityId());
        $createdShipment = $this->getShipment($order);
        $this->assertNotNull($createdShipment->getEntityId());
        $shipmentItems = $createdShipment->getItems();
        $this->assertCount(count($expectedShippedQtyBySku), $shipmentItems);
        foreach ($shipmentItems as $shipmentItem) {
            $this->assertEquals($expectedShippedQtyBySku[$shipmentItem->getSku()], $shipmentItem->getQty());
        }
    }

    /**
     * Data for create full or partial order shipment POST data.
     *
     * @return array
     */
    public function dataForCreateShipmentDataProvider(): array
    {
        return [
            'create_full_shipment' => [
                [
                    'order_increment_id' => '100000001',
                    'items_count_to_ship_by_sku' => [
                        'simple' => 5,
                        'custom-design-simple-product' => 5,
                    ],
                    'comment_text' => 'Create full shipment',
                ],
                [
                    'simple' => 5,
                    'custom-design-simple-product' => 5,
                ],
            ],
            'create_partial_shipment' => [
                [
                    'order_increment_id' => '100000001',
                    'items_count_to_ship_by_sku' => [
                        'simple' => 3,
                        'custom-design-simple-product' => 2,
                    ],
                    'comment_text' => 'Create partial shipment',
                ],
                [
                    'simple' => 3,
                    'custom-design-simple-product' => 2,
                ],
            ],
            'create_shipment_for_one_of_product' => [
                [
                    'order_increment_id' => '100000001',
                    'items_count_to_ship_by_sku' => [
                        'simple' => 4,
                        'custom-design-simple-product' => 0,
                    ],
                    'comment_text' => 'Create partial shipment',
                ],
                [
                    'simple' => 4,
                ],
            ],
            'create_shipment_qty_more_that_ordered' => [
                [
                    'order_increment_id' => '100000001',
                    'items_count_to_ship_by_sku' => [
                        'simple' => 150,
                        'custom-design-simple-product' => 5,
                    ],
                    'comment_text' => 'Create partial shipment',
                ],
                [
                    'simple' => 5,
                    'custom-design-simple-product' => 5,
                ],
            ],
            'create_shipment_qty_less_that_zero' => [
                [
                    'order_increment_id' => '100000001',
                    'items_count_to_ship_by_sku' => [
                        'simple' => -10,
                        'custom-design-simple-product' => 5,
                    ],
                    'comment_text' => 'Create partial shipment',
                ],
                [
                    'custom-design-simple-product' => 5,
                ],
            ],
            'create_shipment_without_counts' => [
                [
                    'order_increment_id' => '100000001',
                    'items_count_to_ship_by_sku' => [],
                    'comment_text' => 'Create partial shipment',
                ],
                [
                    'simple' => 5,
                    'custom-design-simple-product' => 5,
                ],
            ],
        ];
    }

    /**
     * Assert that body contains 404 error and forwarded to no-route if
     * requested POST data is empty.
     *
     * @magentoDataFixture Magento/Sales/_files/order_with_customer.php
     *
     * @return void
     */
    public function testCreateOrderShipmentWithoutPostData(): void
    {
        $this->performShipmentCreationRequest([]);
        $this->assert404NotFound();
    }

    /**
     * Assert that if we doesn't send order_id it will forwarded to no-rout and contains error 404.
     *
     * @magentoDataFixture Magento/Sales/_files/order_with_customer.php
     *
     * @return void
     */
    public function testCreateOrderShipmentWithoutOrderId(): void
    {
        $dataToSend = [
            'order_increment_id' => '100000001',
            'items_count_to_ship_by_sku' => [
                'simple' => 2,
            ],
            'comment_text' => 'Create full shipment',
        ];
        $postData = $this->createPostData($dataToSend);
        unset($postData['order_id']);
        $this->performShipmentCreationRequest($postData);
        $this->assert404NotFound();
    }

    /**
     * Assert that if we send POST data with wrong order item ids we got error message.
     *
     * @magentoDataFixture Magento/Sales/_files/order_with_customer.php
     *
     * @return void
     */
    public function testCreateOrderShipmentWithWrongItemsIds(): void
    {
        $orderId = $this->getOrder('100000001')->getEntityId();
        $postData = [
            'order_id' => $orderId,
            'shipment' =>
                [
                    'items' => [
                        678678 => 4
                    ],
                ],
        ];
        $this->performShipmentCreationRequest($postData);
        $this->assertCreateShipmentRequestWithError(
            "Shipment Document Validation Error(s):\nYou can't create a shipment without products.",
            "admin/order_shipment/new/order_id/{$orderId}"
        );
    }

    /**
     * Assert that if we send POST data with alphabet order item count we got error message.
     *
     * @magentoDataFixture Magento/Sales/_files/order_with_customer.php
     *
     * @return void
     */
    public function testCreateOrderShipmentWithAlphabetQty(): void
    {
        $dataToSend = [
            'order_increment_id' => '100000001',
            'items_count_to_ship_by_sku' => [
                'simple' => 'test_letters',
            ],
            'comment_text' => 'Create full shipment',
        ];
        $postData = $this->createPostData($dataToSend);
        $this->performShipmentCreationRequest($postData);
        $this->assertCreateShipmentRequestWithError(
            "Shipment Document Validation Error(s):\nYou can't create a shipment without products.",
            "admin/order_shipment/new/order_id/{$postData['order_id']}"
        );
    }

    /**
     * @inheritdoc
     */
    public function assert404NotFound()
    {
        $this->assertEquals('noroute', $this->getRequest()->getControllerName());
        $this->assertStringContainsString('404 Error', $this->getResponse()->getBody());
        $this->assertStringContainsString('Page not found', $this->getResponse()->getBody());
    }

    /**
     * Set POST data and type POST to request
     * and perform request by path backend/admin/order_shipment/save.
     *
     * @param array $postData
     * @return void
     */
    private function performShipmentCreationRequest(array $postData): void
    {
        $this->getRequest()->setPostValue($postData)
            ->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/admin/order_shipment/save');
    }

    /**
     * Assert that after create shipment session message manager
     * contain message "The shipment has been created." and redirect to sales/order/view is created.
     *
     * @param int $orderId
     * @return void
     */
    private function assertCreateShipmentRequestSuccessfullyPerformed(int $orderId): void
    {
        $this->assertSessionMessages(
            $this->equalTo([(string)__('The shipment has been created.')]),
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringContains('sales/order/view/order_id/' . $orderId));
    }

    /**
     * Assert that session message manager contain provided error message and
     * redirect created if it was provided.
     *
     * @param string $message
     * @param string|null $redirect
     * @param bool $isNeedEscapeMessage
     * @return void
     */
    private function assertCreateShipmentRequestWithError(
        string $message,
        ?string $redirect = null,
        bool $isNeedEscapeMessage = true
    ): void {
        $assertMessage = $isNeedEscapeMessage
            ? $this->escaper->escapeHtml((string)__($message)) : (string)__($message);
        $this->assertSessionMessages(
            $this->equalTo([$assertMessage]),
            MessageInterface::TYPE_ERROR
        );
        $this->assertRedirect($this->stringContains($redirect));
    }

    /**
     * Create POST data for create shipment for order.
     *
     * @param array $dataForBuildPostData
     * @return array
     */
    private function createPostData(array $dataForBuildPostData): array
    {
        $result = [];
        $order = $this->getOrder($dataForBuildPostData['order_increment_id']);
        $result['order_id'] = (int)$order->getEntityId();
        $shipment = [];
        $shipment['items'] = [];
        foreach ($order->getItems() as $orderItem) {
            if (!isset($dataForBuildPostData['items_count_to_ship_by_sku'][$orderItem->getSku()])) {
                continue;
            }
            $shipment['items'][$orderItem->getItemId()]
                = $dataForBuildPostData['items_count_to_ship_by_sku'][$orderItem->getSku()];
        }
        if (empty($shipment['items'])) {
            unset($shipment['items']);
        }

        $shipment['comment_text'] = $dataForBuildPostData['comment_text'];
        $result['shipment'] = $shipment;

        return $result;
    }
}
