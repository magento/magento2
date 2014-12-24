<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Checkout\Service\V1\Data\PaymentMethod;

use Magento\Checkout\Service\V1\Data\PaymentMethod;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Converter
     */
    protected $converter;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMethodBuilderMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->paymentMethodBuilderMock = $this->getMock(
            '\Magento\Checkout\Service\V1\Data\PaymentMethodBuilder', ['populateWithArray', 'create'], [], '', false
        );

        $this->converter = $this->objectManager->getObject(
            'Magento\Checkout\Service\V1\Data\PaymentMethod\Converter',
            [
                'builder' => $this->paymentMethodBuilderMock,
            ]
        );
    }

    public function testConvertQuotePaymentObjectToPaymentDataObject()
    {
        $methodMock = $this->getMock('\Magento\Payment\Model\Method\AbstractMethod', [], [], '', false);
        $methodMock->expects($this->once())->method('getCode')->will($this->returnValue('paymentCode'));
        $methodMock->expects($this->once())->method('getTitle')->will($this->returnValue('paymentTitle'));

        $data = [
            PaymentMethod::TITLE => 'paymentTitle',
            PaymentMethod::CODE => 'paymentCode',
        ];

        $this->paymentMethodBuilderMock->expects($this->once())
            ->method('populateWithArray')
            ->with($data)
            ->will($this->returnSelf());

        $paymentMethodMock = $this->getMock('\Magento\Checkout\Service\V1\Data\PaymentMethod', [], [], '', false);

        $this->paymentMethodBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($paymentMethodMock));

        $this->assertEquals($paymentMethodMock, $this->converter->toDataObject($methodMock));
    }
}
