<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Ui\Component\Listing\Column;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Ui\Component\Listing\Column\Price;

/**
 * Class PurchasedPriceTest
 */
class PurchasedPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Price
     */
    protected $model;

    /**
     * @var PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceFormatterMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->getMockForAbstractClass();
        $processor = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->never())->method('getProcessor')->willReturn($processor);
        $this->priceFormatterMock = $this->getMockForAbstractClass(
            \Magento\Framework\Pricing\PriceCurrencyInterface::class
        );
        $this->model = $objectManager->getObject(
            \Magento\Sales\Ui\Component\Listing\Column\PurchasedPrice::class,
            ['priceFormatter' => $this->priceFormatterMock, 'context' => $contextMock]
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
                    [
                        $itemName => $oldItemValue,
                        'order_currency_code' => 'US'
                    ]
                ]
            ]
        ];

        $this->priceFormatterMock->expects($this->once())
            ->method('format')
            ->with($oldItemValue, false, null, null, 'US')
            ->willReturn($newItemValue);

        $this->model->setData('name', $itemName);
        $dataSource = $this->model->prepareDataSource($dataSource);
        $this->assertEquals($newItemValue, $dataSource['data']['items'][0][$itemName]);
    }
}
