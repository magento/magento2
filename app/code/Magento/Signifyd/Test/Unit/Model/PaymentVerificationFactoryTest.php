<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Payment\Api\PaymentVerificationInterface;
use Magento\Signifyd\Model\PaymentVerificationFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Payment\Gateway\ConfigInterface;

class PaymentVerificationFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentVerificationFactory
     */
    private $factory;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $fakeObjectManager;

    /**
     * @var ConfigInterface|MockObject
     */
    private $config;

    /**
     * @var PaymentVerificationInterface|MockObject
     */
    private $avsDefaultAdapter;

    /**
     * @var PaymentVerificationInterface|MockObject
     */
    private $cvvDefaultAdapter;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->fakeObjectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->config = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->avsDefaultAdapter = $this->getMockBuilder(PaymentVerificationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cvvDefaultAdapter = $this->getMockBuilder(PaymentVerificationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->factory = $this->objectManager->getObject(PaymentVerificationFactory::class, [
            'objectManager' => $this->fakeObjectManager,
            'config' => $this->config,
            'avsDefaultAdapter' => $this->avsDefaultAdapter,
            'cvvDefaultAdapter' => $this->cvvDefaultAdapter
        ]);
    }

    /**
     * Checks a test case when factory creates CVV mapper for provided payment method.
     *
     * @covers \Magento\Signifyd\Model\PaymentVerificationFactory::createPaymentCvv
     */
    public function testCreatePaymentCvv()
    {
        $paymentMethodCode = 'exists_payment';

        $this->config->expects($this->once())
            ->method('setMethodCode')
            ->with($this->equalTo($paymentMethodCode))
            ->willReturnSelf();

        $this->config->expects($this->once())
            ->method('getValue')
            ->with('cvv_ems_adapter')
            ->willReturn(PaymentVerificationInterface::class);

        /** @var PaymentVerificationInterface|MockObject $cvvAdapter */
        $cvvAdapter = $this->getMockBuilder(PaymentVerificationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fakeObjectManager->expects($this->once())
            ->method('create')
            ->with($this->equalTo(PaymentVerificationInterface::class))
            ->willReturn($cvvAdapter);

        $mapper = $this->factory->createPaymentCvv($paymentMethodCode);
        $this->assertInstanceOf(PaymentVerificationInterface::class, $mapper);
    }

    /**
     * Checks a test case, when provided payment method does not have cvv mapper.
     *
     * @covers \Magento\Signifyd\Model\PaymentVerificationFactory::createPaymentCvv
     */
    public function testCreateDefaultCvvMapper()
    {
        $paymentMethodCode = 'non_exists_payment';

        $this->config->expects($this->once())
            ->method('setMethodCode')
            ->with($this->equalTo($paymentMethodCode))
            ->willReturnSelf();

        $this->config->expects($this->once())
            ->method('getValue')
            ->with('cvv_ems_adapter')
            ->willReturn(null);

        $this->fakeObjectManager->expects($this->never())
            ->method('create');

        $mapper = $this->factory->createPaymentCvv($paymentMethodCode);
        $this->assertSame($this->cvvDefaultAdapter, $mapper);
    }

    /**
     * Checks a test case, when mapper implementation does not corresponding to PaymentVerificationInterface.
     *
     * @covers \Magento\Signifyd\Model\PaymentVerificationFactory::createPaymentCvv
     * @expectedException \Magento\Framework\Exception\ConfigurationMismatchException
     * @expectedExceptionMessage stdClass must implement Magento\Payment\Api\PaymentVerificationInterface
     */
    public function testCreateWithUnsupportedImplementation()
    {
        $paymentMethodCode = 'exists_payment';

        $this->config->expects($this->once())
            ->method('setMethodCode')
            ->with($this->equalTo($paymentMethodCode))
            ->willReturnSelf();

        $this->config->expects($this->once())
            ->method('getValue')
            ->with('cvv_ems_adapter')
            ->willReturn(\stdClass::class);

        $cvvAdapter = new \stdClass();
        $this->fakeObjectManager->expects($this->once())
            ->method('create')
            ->with($this->equalTo(\stdClass::class))
            ->willReturn($cvvAdapter);

        $this->factory->createPaymentCvv($paymentMethodCode);
    }

    /**
     * Checks a test case when factory creates AVS mapper for provided payment method.
     *
     * @covers \Magento\Signifyd\Model\PaymentVerificationFactory::createPaymentAvs
     */
    public function testCreatePaymentAvs()
    {
        $paymentMethodCode = 'exists_payment';

        $this->config->expects($this->once())
            ->method('setMethodCode')
            ->with($this->equalTo($paymentMethodCode))
            ->willReturnSelf();

        $this->config->expects($this->once())
            ->method('getValue')
            ->with('avs_ems_adapter')
            ->willReturn(PaymentVerificationInterface::class);

        $avsAdapter = $this->getMockBuilder(PaymentVerificationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fakeObjectManager->expects($this->once())
            ->method('create')
            ->with($this->equalTo(PaymentVerificationInterface::class))
            ->willReturn($avsAdapter);

        $mapper = $this->factory->createPaymentAvs($paymentMethodCode);
        $this->assertInstanceOf(PaymentVerificationInterface::class, $mapper);
    }

    /**
     * Checks a test case when provided payment method does not support
     */
    public function testCreateDefaultAvsMapper()
    {
        $paymentMethodCode = 'non_exists_payment';

        $this->config->expects($this->once())
            ->method('setMethodCode')
            ->with($this->equalTo($paymentMethodCode))
            ->willReturnSelf();

        $this->config->expects($this->once())
            ->method('getValue')
            ->with('avs_ems_adapter')
            ->willReturn(null);

        $this->fakeObjectManager->expects($this->never())
            ->method('create');

        $mapper = $this->factory->createPaymentAvs($paymentMethodCode);
        $this->assertSame($this->avsDefaultAdapter, $mapper);
    }
}
