<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Quote\Model\Quote\Payment;

use Magento\Payment\Model\Method\Substitution;
use Magento\TestFramework\Helper\ObjectManager;

/**
 * Class ToOrderPaymentTest tests converter to order payment
 */
class ToOrderPaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Api\Data\OrderPaymentDataBuilder | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderPaymentBuilderMock;

    /**
     * @var \Magento\Framework\Object\Copy | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectCopyMock;

    /**
     * @var \Magento\Quote\Model\Quote\Payment | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMock;

    /**
     * @var \Magento\Quote\Model\Quote\Payment\ToOrderPayment
     */
    protected $converter;

    public function setUp()
    {
        $this->paymentMock = $this->getMock('Magento\Quote\Model\Quote\Payment', [], [], '', false);
        $this->objectCopyMock = $this->getMock('Magento\Framework\Object\Copy', [], [], '', false);
        $this->orderPaymentBuilderMock = $this->getMock(
            'Magento\Sales\Api\Data\OrderPaymentDataBuilder',
            ['populateWithArray', 'create', 'setAdditionalInformation'],
            [],
            '',
            false
        );
        $objectManager = new ObjectManager($this);
        $this->converter = $objectManager->getObject(
            'Magento\Quote\Model\Quote\Payment\ToOrderPayment',
            [
                'orderPaymentBuilder' => $this->orderPaymentBuilderMock,
                'objectCopyService' => $this->objectCopyMock
            ]
        );
    }

    /**
     * Tests Convert method in payment to order payment converter
     */
    public function testConvert()
    {
        $methodInterface = $this->getMock('Magento\Payment\Model\MethodInterface', [], [], '', false);
        $orderPayment = $this->getMock('Magento\Sales\Api\Data\OrderPaymentInterface', [], [], '', false);
        $paymentData = ['test' => 'test2'];
        $data = ['some_id' => 1];
        $paymentMethodTitle = 'TestTitle';
        $this->paymentMock->expects($this->once())->method('getMethodInstance')->willReturn($methodInterface);
        $methodInterface->expects($this->once())->method('getTitle')->willReturn($paymentMethodTitle);
        $this->objectCopyMock->expects($this->once())->method('getDataFromFieldset')->with(
            'quote_convert_payment',
            'to_order_payment',
            $this->paymentMock
        )->willReturn($paymentData);
        $this->orderPaymentBuilderMock->expects($this->once())
            ->method('populateWithArray')
            ->with(array_merge($paymentData, $data))
            ->willReturnSelf();

        $this->orderPaymentBuilderMock->expects($this->once())
            ->method('setAdditionalInformation')
            ->with(serialize([Substitution::INFO_KEY_TITLE => $paymentMethodTitle]))
            ->willReturnSelf();
        $this->orderPaymentBuilderMock->expects($this->once())->method('create')->willReturn($orderPayment);
        $this->assertSame($orderPayment, $this->converter->convert($this->paymentMock, $data));
    }
}
