<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Service\V1;

/**
 * API test for creation of Invoice for certain Order.
 */
class OrderInvoiceCreateTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    const SERVICE_READ_NAME = 'salesInvoiceOrderV1';
    const SERVICE_VERSION = 'V1';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->invoiceRepository = $this->objectManager->get(
            \Magento\Sales\Api\InvoiceRepositoryInterface::class
        );
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/order_new.php
     */
    public function testInvoiceCreate()
    {
        /** @var \Magento\Sales\Model\Order $existingOrder */
        $existingOrder = $this->objectManager->create(\Magento\Sales\Model\Order::class)
            ->loadByIncrementId('100000001');

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/order/' . $existingOrder->getId() . '/invoice',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'execute',
            ],
        ];

        $requestData = [
            'orderId' => $existingOrder->getId(),
            'items' => [],
            'comment' => [
                'comment' => 'Test Comment',
                'is_visible_on_front' => 1,
            ],
        ];

        /** @var \Magento\Sales\Api\Data\OrderItemInterface $item */
        foreach ($existingOrder->getAllItems() as $item) {
            $requestData['items'][] = [
                'order_item_id' => $item->getItemId(),
                'qty' => $item->getQtyOrdered(),
            ];
        }

        $result = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertNotEmpty($result);

        try {
            $this->invoiceRepository->get($result);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->fail('Failed asserting that Invoice was created');
        }

        /** @var \Magento\Sales\Model\Order $updatedOrder */
        $updatedOrder = $this->objectManager->create(\Magento\Sales\Model\Order::class)
            ->loadByIncrementId('100000001');

        $this->assertNotEquals(
            $existingOrder->getStatus(),
            $updatedOrder->getStatus(),
            'Failed asserting that Order status was changed'
        );
    }

    /**
     * Tests that MAGETWO-95346 was fixed for bundled products
     *
     * @codingStandardsIgnoreStart
     * @codingStandardsIgnoreEnd
     * @magentoApiDataFixture Magento/Sales/_files/order_with_bundle.php
     */
    public function testOrderWithBundleInvoicedWithInvalidQuantitiesReturnsError()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches(
            '/Invoice Document Validation Error\\(s\\):(?:\\n|\\\\n)'
            . 'The invoice can\'t be created without products. Add products and try again./'
        );

        /** @var \Magento\Sales\Model\Order $existingOrder */
        $existingOrder = $this->objectManager->create(\Magento\Sales\Model\Order::class)
            ->loadByIncrementId('100000001');

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/order/' . $existingOrder->getId() . '/invoice',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'execute',
            ],
        ];

        $requestData = [
            'orderId' => $existingOrder->getId(),
            'notify' => true,
            'appendComment' => true,
            'items' => [
                [
                    'order_item_id' => -1,
                    'qty' => 1
                ]
            ],
            'comment' => [
                'comment' => 'Test offline',
                'isVisibleOnFront' => 1,
            ],
        ];

        $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * Tests that MAGETWO-95346 was fixed for configurable products
     *
     * @codingStandardsIgnoreStart
     * @codingStandardsIgnoreEnd
     * @magentoApiDataFixture Magento/Sales/_files/order_configurable_product.php
     */
    public function testOrderWithConfigurableProductInvoicedWithInvalidQuantitiesReturnsError()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches(
            '/Invoice Document Validation Error\\(s\\):(?:\\n|\\\\n)'
            . 'The invoice can\'t be created without products. Add products and try again./'
        );

        /** @var \Magento\Sales\Model\Order $existingOrder */
        $existingOrder = $this->objectManager->create(\Magento\Sales\Model\Order::class)
            ->loadByIncrementId('100000001');

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/order/' . $existingOrder->getId() . '/invoice',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'execute',
            ],
        ];

        $requestData = [
            'orderId' => $existingOrder->getId(),
            'notify' => true,
            'appendComment' => true,
            'items' => [
                [
                    'order_item_id' => -1,
                    'qty' => 1
                ]
            ],
            'comment' => [
                'comment' => 'Test offline',
                'isVisibleOnFront' => 1,
            ],
        ];

        $this->_webApiCall($serviceInfo, $requestData);
    }
}
