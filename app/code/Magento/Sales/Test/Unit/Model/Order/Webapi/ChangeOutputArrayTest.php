<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Webapi;

use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer;
use Magento\Sales\Model\Order\Webapi\ChangeOutputArray;

/**
 * Test for Magento\Sales\Model\Order\Webapi\ChangeOutputArray class.
 */
class ChangeOutputArrayTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DefaultColumn|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceRendererMock;

    /**
     * @var DefaultRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $defaultRendererMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ChangeOutputArray
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->priceRendererMock = $this->createMock(DefaultColumn::class);
        $this->defaultRendererMock = $this->createMock(DefaultRenderer::class);

        $this->model = $this->objectManager->getObject(
            ChangeOutputArray::class,
            [
                'priceRenderer' => $this->priceRendererMock,
                'defaultRenderer' => $this->defaultRendererMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $expectedResult = [
            OrderItemInterface::ROW_TOTAL => 10,
            OrderItemInterface::BASE_ROW_TOTAL => 10,
            OrderItemInterface::ROW_TOTAL_INCL_TAX => 11,
            OrderItemInterface::BASE_ROW_TOTAL_INCL_TAX => 11,
        ];
        $orderItemInterfaceMock = $this->createMock(OrderItemInterface::class);

        $this->priceRendererMock->expects($this->once())
            ->method('getTotalAmount')
            ->with($orderItemInterfaceMock)
            ->willReturn(10);
        $this->priceRendererMock->expects($this->once())
            ->method('getBaseTotalAmount')
            ->with($orderItemInterfaceMock)
            ->willReturn(10);
        $this->defaultRendererMock->expects($this->once())
            ->method('getTotalAmount')
            ->with($orderItemInterfaceMock)
            ->willReturn(11);
        $this->defaultRendererMock->expects($this->once())
            ->method('getBaseTotalAmount')
            ->with($orderItemInterfaceMock)
            ->willReturn(11);

        $this->assertEquals($expectedResult, $this->model->execute($orderItemInterfaceMock, []));
    }
}
