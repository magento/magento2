<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Ui\Component\Listing\Column;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\MethodInterface;
use Magento\Sales\Ui\Component\Listing\Column\PaymentMethod;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PaymentMethodTest extends TestCase
{
    /**
     * @var PaymentMethod
     */
    protected $model;

    /**
     * @var Data|MockObject
     */
    protected $paymentHelper;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $contextMock = $this->getMockBuilder(ContextInterface::class)
            ->getMockForAbstractClass();
        $processor = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->never())->method('getProcessor')->willReturn($processor);
        $this->paymentHelper = $this->createMock(Data::class);
        $this->model = $objectManager->getObject(
            PaymentMethod::class,
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

        $payment = $this->getMockForAbstractClass(MethodInterface::class);
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
