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
use Magento\Sales\Ui\Component\Listing\Column\Price;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PriceTest extends TestCase
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
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

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
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManager->getObject(
            Price::class,
            ['currency' => $this->currencyMock, 'context' => $contextMock, 'storeManager' => $this->storeManagerMock]
        );
    }

    /**
     * @param $hasCurrency
     * @param $dataSource
     * @param $currencyCode
     * @dataProvider testPrepareDataSourceDataProvider
     */
    public function testPrepareDataSource($hasCurrency, $dataSource, $currencyCode)
    {
        $itemName = 'itemName';
        $oldItemValue = 'oldItemValue';
        $newItemValue = 'newItemValue';

        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $currencyMock = $this->getMockBuilder(Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
        $currencyMock->expects($hasCurrency ? $this->never() : $this->once())
            ->method('getCurrencyCode')
            ->willReturn($currencyCode);
        $this->storeManagerMock->expects($hasCurrency ? $this->never() : $this->once())
            ->method('getStore')
            ->willReturn($store);
        $store->expects($hasCurrency ? $this->never() : $this->once())
            ->method('getBaseCurrency')
            ->willReturn($currencyMock);

        $this->currencyMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();

        $this->currencyMock->expects($this->once())
            ->method('format')
            ->with($oldItemValue, [], false)
            ->willReturn($newItemValue);

        $this->model->setData('name', $itemName);
        $dataSource = $this->model->prepareDataSource($dataSource);
        $this->assertEquals($newItemValue, $dataSource['data']['items'][0][$itemName]);
    }

    public function testPrepareDataSourceDataProvider()
    {
        $dataSource1 = [
            'data' => [
                'items' => [
                    [
                        'itemName' => 'oldItemValue',
                        'base_currency_code' => 'US'
                    ]
                ]
            ]
        ];
        $dataSource2 = [
            'data' => [
                'items' => [
                    [
                        'itemName' => 'oldItemValue'
                    ]
                ]
            ]
        ];
        return [
            [true, $dataSource1, 'US'],
            [false, $dataSource2, 'SAR'],
        ];
    }
}
