<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model;

class VoidTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/Paypal/_files/order_payflowpro.php
     * @magentoConfigFixture current_store payment/payflowpro/active 1
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPayflowProVoid()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var $order \Magento\Sales\Model\Order */
        $order = $objectManager->create('Magento\Sales\Model\Order');
        $order->loadByIncrementId('100000001');
        $payment = $order->getPayment();

        $gatewayMock = $this->getMock(
            'Magento\Paypal\Model\Payflow\Service\Gateway',
            [],
            [],
            '',
            false
        );

        $configMock = $this->getMock(
            'Magento\Paypal\Model\PayflowConfig',
            [],
            [],
            '',
            false
        );
        $configFactoryMock = $this->getMock(
            'Magento\Payment\Model\Method\ConfigInterfaceFactory',
            ['create'],
            [],
            '',
            false
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

        /** @var \Magento\Paypal\Model\Payflowpro|\PHPUnit_Framework_MockObject_MockObject $instance */
        $instance = $this->getMock(
            'Magento\Paypal\Model\Payflowpro',
            ['setStore'],
            [
                $objectManager->get('Magento\Framework\Model\Context'),
                $objectManager->get('Magento\Framework\Registry'),
                $objectManager->get('Magento\Framework\Api\ExtensionAttributesFactory'),
                $objectManager->get('Magento\Framework\Api\AttributeValueFactory'),
                $objectManager->get('Magento\Payment\Helper\Data'),
                $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface'),
                $objectManager->get('Magento\Payment\Model\Method\Logger'),
                $objectManager->get('Magento\Framework\Module\ModuleListInterface'),
                $objectManager->get('Magento\Framework\Stdlib\DateTime\TimezoneInterface'),
                $objectManager->get('Magento\Store\Model\StoreManagerInterface'),
                $configFactoryMock,
                $gatewayMock,
                $objectManager->get('Magento\Paypal\Model\Payflow\Service\Response\Handler\HandlerInterface'),
                null,
                null,
                []
            ]
        );

        $response = new \Magento\Framework\DataObject(
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

        $gatewayMock->expects($this->once())
            ->method('postRequest')
            ->willReturn($response);
        $instance->expects($this->once())
            ->method('setStore')
            ->willReturnSelf();

        $payment->setMethodInstance($instance);
        $payment->void(new \Magento\Framework\DataObject());
        $order->save();

        $order = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order');
        $order->loadByIncrementId('100000001');
        $this->assertFalse($order->canVoidPayment());
    }
}
