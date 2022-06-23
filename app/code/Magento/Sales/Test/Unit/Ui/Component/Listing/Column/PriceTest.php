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

/**
 * Contains tests for Price class
 */
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

    /**
     * @inheritDoc
     */
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
     * Test for prepareDataSource method
     *
     * @param bool $hasCurrency
     * @param array $dataSource
     * @param string $currencyCode
     * @param int|null $expectedStoreId
     * @dataProvider testPrepareDataSourceDataProvider
     */
    public function testPrepareDataSource(
        bool $hasCurrency,
        array $dataSource,
        string $currencyCode,
        ?int $expectedStoreId = null
    ): void {
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
            ->with($expectedStoreId)
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

    /**
     * Provider for testPrepareDataSource
     *
     * @return array
     */
    public function testPrepareDataSourceDataProvider(): array
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
        $dataSource3 = [
            'data' => [
                'items' => [
                    [
                        'itemName' => 'oldItemValue',
                        'store_id' => '2'
                    ]
                ]
            ]
        ];
        $dataSource4 = [
            'data' => [
                'items' => [
                    [
                        'itemName' => 'oldItemValue',
                        'store_id' => 'abc'
                    ]
                ]
            ]
        ];
        $dataSource5 = [
            'data' => [
                'items' => [
                    [
                        'itemName' => 'oldItemValue',
                        'store_id' => '123Test',
                        'base_currency_code' => '',
                    ]
                ]
            ]
        ];

        return [
            [true, $dataSource1, 'US'],
            [false, $dataSource2, 'SAR'],
            [false, $dataSource3, 'SAR', 2],
            [false, $dataSource4, 'SAR'],
            [false, $dataSource5, 'INR'],
        ];
    }
}
