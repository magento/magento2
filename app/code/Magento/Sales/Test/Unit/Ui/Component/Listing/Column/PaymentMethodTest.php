<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Ui\Component\Listing\Column;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Helper\Data;
use Magento\Sales\Ui\Component\Listing\Column\PaymentMethod;

/**
 * Class PaymentMethodTest
 */
class PaymentMethodTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PaymentMethod
     */
    protected $model;

    /**
     * @var Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentHelper;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $contextMock = $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\ContextInterface')
            ->getMockForAbstractClass();
        $processor = $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\Processor')
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())->method('getProcessor')->willReturn($processor);
        $this->paymentHelper = $this->getMock('Magento\Payment\Helper\Data', [], [], '', false);
        $this->model = $objectManager->getObject(
            'Magento\Sales\Ui\Component\Listing\Column\PaymentMethod',
            ['paymentHelper' => $this->paymentHelper, 'context' => $contextMock]
        );
    }

    public function testPrepareDataSource()
    {
        $itemName = 'itemName';
        $oldItemValue = 'oldItemValue';
        $newItemValue = 'newItemValue';
        $dataSource = [
            'data' => [
                'items' => [
                    [$itemName => $oldItemValue]
                ]
            ]
        ];

        $payment = $this->getMockForAbstractClass('Magento\Payment\Model\MethodInterface');
        $payment->expects($this->once())
            ->method('getTitle')
            ->willReturn($newItemValue);
        $this->paymentHelper->expects($this->once())
            ->method('getMethodInstance')
            ->with($oldItemValue)
            ->willReturn($payment);

        $this->model->setData('name', $itemName);
        $dataSource = $this->model->prepareDataSource($dataSource);
        $this->assertEquals($newItemValue, $dataSource['data']['items'][0][$itemName]);
    }
}
