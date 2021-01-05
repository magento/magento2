<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Ui\Component\Listing\Column;

use Magento\Directory\Model\Currency;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Ui\Component\Listing\Column\Price;
use Magento\Sales\Ui\Component\Listing\Column\PurchasedPrice;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PurchasedPriceTest extends TestCase
{
    /**
     * @var Price
     */
    protected $model;

    /**
     * @var Currency|MockObject
     */
    protected $currencyMock;

    /**
     * @var OrderRepositoryInterface|MockObject
     */
    protected $orderMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $contextMock = $this->getMockBuilder(ContextInterface::class)
            ->getMockForAbstractClass();
        $processor = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->never())->method('getProcessor')->willReturn($processor);
        $this->currencyMock = $this->getMockBuilder(Currency::class)
            ->setMethods(['load', 'format'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderMock = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->setMethods(['getList','get','delete','save','getOrderCurrencyCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManager->getObject(
            PurchasedPrice::class,
            [
                'currency' => $this->currencyMock,
                'context' => $contextMock,
                'order' => $this->orderMock,
            ]
        );
    }

    /**
     * @param string $itemName
     * @param string $oldItemValue
     * @param string $newItemValue
     * @param string|null $orderCurrencyCode
     * @dataProvider prepareDataSourceDataProvider
     */
    public function testPrepareDataSource(
        $itemName,
        $oldItemValue,
        $newItemValue,
        $orderCurrencyCode
    ): void {
        $dataSource = [
            'data' => [
                'items' => [
                    [
                        $itemName => $oldItemValue,
                        'order_currency_code' => $orderCurrencyCode,
                        'order_id' => 1,
                    ]
                ]
            ]
        ];

        if (isset($dataSource['data']['items'][0]['order_currency_code'])) {
            $currencyCode = $dataSource['data']['items'][0]['order_currency_code'];
        } else {
            $currencyCode = 'FR';
            $this->orderMock->expects($this->once())
                ->method('get')
                ->willReturnSelf();
            $this->orderMock->expects($this->once())
                ->method('getOrderCurrencyCode')
                ->willReturn($currencyCode);
        }

        $this->currencyMock->expects($this->once())
            ->method('load')
            ->with($currencyCode)
            ->willReturnSelf();

        $this->currencyMock->expects($this->once())
            ->method('format')
            ->with($oldItemValue, [], false)
            ->willReturn($newItemValue);

        $this->model->setData('name', $itemName);
        $dataSource = $this->model->prepareDataSource($dataSource);
        $this->assertEquals($newItemValue, $dataSource['data']['items'][0][$itemName]);
    }

    /**
     * @return array
     */
    public function prepareDataSourceDataProvider(): array
    {
        return [
            [
                'item_name' => 'itemName',
                'old_item_value' => 'oldItemValue',
                'new_item_value' => 'newItemValue',
                'order_currency_code' => 'US',
            ],
            [
                'item_name' => 'itemName',
                'old_item_value' => 'oldItemValue',
                'new_item_value' => 'newItemValue',
                'order_currency_code' => null,
            ],
        ];
    }
}
