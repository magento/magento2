<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Model\Context;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\ConfigInterfaceFactory;
use Magento\Payment\Model\Method\Logger;
use Magento\Paypal\Model\Payflow\Service\Gateway;
use Magento\Paypal\Model\Payflow\Service\Response\Handler\HandlerInterface;
use Magento\Quote\Api\Data\PaymentMethodInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PayflowproVoidTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Tests PayflowPro payment void operation.
     *
     * @magentoDataFixture Magento/Paypal/_files/order_payflowpro.php
     * @magentoConfigFixture current_store payment/payflowpro/active 1
     */
    public function testPaymentVoid()
    {
        $response = new DataObject(
            [
                'result' => '0',
                'pnref' => 'V19A3D27B61E',
                'respmsg' => 'Approved',
                'authcode' => '510PNI',
                'hostcode' => 'A',
                'request_id' => 'f930d3dc6824c1f7230c5529dc37ae5e',
                'result_code' => '0',
            ]
        );

        $order = $this->getOrder();
        $payment = $order->getPayment();
        $instance = $this->getPaymentMethodInstance($response);
        $payment->setMethodInstance($instance);

        $this->assertTrue($order->canVoidPayment());

        $payment->void(new DataObject());
        /** @var OrderRepositoryInterface $orderRepository */
        $orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $orderRepository->save($order);

        $order = $this->getOrderByIncrementId('100000001');
        $this->assertFalse($order->canVoidPayment());
    }

    /**
     * Tests canceling order with acceptable void transaction results.
     *
     * @param DataObject $response
     * @magentoDataFixture Magento/Paypal/_files/order_payflowpro.php
     * @magentoConfigFixture current_store payment/payflowpro/active 1
     * @dataProvider orderCancelSuccessDataProvider
     */
    public function testOrderCancelSuccess(DataObject $response)
    {
        $order = $this->getOrder();
        $payment = $order->getPayment();
        $instance = $this->getPaymentMethodInstance($response);
        $payment->setMethodInstance($instance);
        $order->cancel();

        $this->assertEquals(Order::STATE_CANCELED, $order->getState());
        $this->assertEquals(Order::STATE_CANCELED, $order->getStatus());
    }

    /**
     * @return array
     */
    public function orderCancelSuccessDataProvider(): array
    {
        return [
            'Authorization has expired' => [
                new DataObject(
                    [
                        'respmsg' => 'Declined: 10601-Authorization has expired.',
                        'result_code' => '10601',
                    ]
                )
            ],
            'Authorization voided successfully' => [
                new DataObject(
                    [
                        'respmsg' => 'Approved',
                        'result_code' => '0',
                    ]
                )
            ]
        ];
    }

    /**
     * Tests canceling the order when got an error during transaction voiding.
     *
     * @magentoDataFixture Magento/Paypal/_files/order_payflowpro.php
     * @magentoConfigFixture current_store payment/payflowpro/active 1
     */
    public function testOrderCancelWithVoidError()
    {
        $response = new DataObject(
            [
                'respmsg' => 'Declined: for some reason other then expired authorization',
                'result_code' => '111',
            ]
        );
        $order = $this->getOrder();
        $payment = $order->getPayment();
        $instance = $this->getPaymentMethodInstance($response);
        $payment->setMethodInstance($instance);

        $this->expectException(CommandException::class);
        $order->cancel();
    }

    /**
     * Returns prepared order.
     *
     * @return Order
     * @throws \ReflectionException
     */
    private function getOrder(): Order
    {
        /** @var $order Order */
        $order = $this->getOrderByIncrementId('100000001');
        $orderItem = $this->createMock(Item::class);
        $orderItem->method('getQtyToInvoice')
            ->willReturn(true);
        $order->setItems([$orderItem]);

        $payment = $order->getPayment();
        $canVoidLookupProperty = new \ReflectionProperty(get_class($payment), '_canVoidLookup');
        $canVoidLookupProperty->setAccessible(true);
        $canVoidLookupProperty->setValue($payment, true);

        return $order;
    }

    /**
     * Returns payment method instance.
     *
     * @param DataObject $response
     * @return PaymentMethodInterface
     * @throws \ReflectionException
     */
    private function getPaymentMethodInstance(DataObject $response): PaymentMethodInterface
    {
        $gatewayMock = $this->createMock(Gateway::class);
        $gatewayMock->expects($this->once())
            ->method('postRequest')
            ->willReturn($response);

        $configMock = $this->createMock(PayflowConfig::class);
        $configFactoryMock = $this->createPartialMock(
            ConfigInterfaceFactory::class,
            ['create']
        );

        $configFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($configMock);

        $configMock->expects($this->any())
            ->method('getValue')
            ->willReturnMap(
                [
                    ['use_proxy', false],
                    ['sandbox_flag', '1'],
                    ['transaction_url_test_mode', 'https://test_transaction_url']
                ]
            );

        /** @var Payflowpro|\PHPUnit\Framework\MockObject\MockObject $instance */
        $instance = $this->getMockBuilder(Payflowpro::class)
            ->setMethods(['setStore', 'getInfoInstance'])
            ->setConstructorArgs(
                [
                    $this->objectManager->get(Context::class),
                    $this->objectManager->get(Registry::class),
                    $this->objectManager->get(ExtensionAttributesFactory::class),
                    $this->objectManager->get(AttributeValueFactory::class),
                    $this->objectManager->get(Data::class),
                    $this->objectManager->get(ScopeConfigInterface::class),
                    $this->objectManager->get(Logger::class),
                    $this->objectManager->get(ModuleListInterface::class),
                    $this->objectManager->get(TimezoneInterface::class),
                    $this->objectManager->get(StoreManagerInterface::class),
                    $configFactoryMock,
                    $gatewayMock,
                    $this->objectManager->get(HandlerInterface::class),
                    null,
                    null,
                    []
                ]
            )
            ->getMock();

        $instance->expects($this->once())
            ->method('setStore')
            ->willReturnSelf();
        $paymentInfoInstance = $this->getMockForAbstractClass(InfoInterface::class);
        $instance->method('getInfoInstance')
            ->willReturn($paymentInfoInstance);

        return $instance;
    }

    /**
     * Get stored order.
     *
     * @param string $incrementId
     * @return OrderInterface
     */
    private function getOrderByIncrementId(string $incrementId)
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter(OrderInterface::INCREMENT_ID, $incrementId)
            ->create();

        $orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $orders = $orderRepository->getList($searchCriteria)
            ->getItems();

        /** @var OrderInterface $order */
        return array_pop($orders);
    }
}
