<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model;

use Magento\Framework\DataObject;
use Magento\Paypal\Model\Api\Nvp;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\Hostedpro;
use Magento\Paypal\Model\Hostedpro\RequestFactory;
use Magento\Paypal\Model\ProFactory;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class HostedproTest
 * @package Magento\Paypal\Model
 */
class HostedproTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Paypal\Model\Hostedpro
     */
    private $model;

    /**
     * @var \Magento\Paypal\Model\Api\Nvp|\PHPUnit_Framework_MockObject_MockObject
     */
    private $api;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->api = $this->getMockBuilder(Nvp::class)
            ->disableOriginalConstructor()
            ->setMethods(['call'])
            ->getMock();

        $proFactory = $this->getProFactory();

        $this->model = $this->objectManager
            ->create(Hostedpro::class, [
                'proFactory' => $proFactory
            ]);
    }

    /**
     * @covers \Magento\Paypal\Model\Hostedpro::initialize
     * @magentoDataFixture Magento/Paypal/_files/order_hostedpro.php
     */
    public function testInitialize()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create(Order::class);
        $order->loadByIncrementId('100000001');
        $payment = $order->getPayment();

        $this->model->setInfoInstance($payment);

        $this->api->expects(static::once())
            ->method('call')
            ->willReturn([
                'EMAILLINK' => 'https://securepayments.sandbox.paypal.com/webapps/HostedSoleSolutionApp/webflow/'
            ]);

        $state = $this->objectManager->create(DataObject::class);
        $this->model->initialize(Config::PAYMENT_ACTION_AUTH, $state);

        static::assertEquals(Order::STATE_PENDING_PAYMENT, $state->getState());
        static::assertEquals(Order::STATE_PENDING_PAYMENT, $state->getStatus());
        static::assertFalse($state->getIsNotified());
    }

    /**
     * Get mock for config
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getConfig()
    {
        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();
        $config->expects(static::any())
            ->method('getValue')
            ->with('payment_action')
            ->willReturn(Config::PAYMENT_ACTION_AUTH);
        return $config;
    }

    /**
     * Create mock for Pro factory
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getProFactory()
    {
        $pro = $this->getMockBuilder(Pro::class)
            ->disableOriginalConstructor()
            ->setMethods(['getApi', 'setMethod', 'getConfig', '__wakeup'])
            ->getMock();

        $config = $this->getConfig();
        $pro->expects(static::any())
            ->method('getConfig')
            ->willReturn($config);
        $pro->expects(static::any())
            ->method('getApi')
            ->willReturn($this->api);

        $proFactory = $this->getMockBuilder(ProFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $proFactory->expects(static::once())
            ->method('create')
            ->willReturn($pro);
        return $proFactory;
    }
}
