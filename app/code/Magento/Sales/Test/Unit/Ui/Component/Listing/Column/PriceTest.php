<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Ui\Component\Listing\Column;

use Magento\Directory\Model\Currency;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Sales\Ui\Component\Listing\Column\Price;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

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
            ->getMock();
        $processor = $this->createMock(Processor::class);
        $contextMock->expects($this->never())->method('getProcessor')->willReturn($processor);
        $this->currencyMock = $this->getMockBuilder(Currency::class)
            ->onlyMethods(['load', 'format'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
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
     */
    #[DataProvider('prepareDataSourceDataProvider')]
    public function testPrepareDataSource(
        bool $hasCurrency,
        array $dataSource,
        string $currencyCode,
        ?int $expectedStoreId = null
    ): void {
         $itemName = 'itemName';
         $oldItemValue = 'oldItemValue';
         $newItemValue = 'newItemValue';

         $store = $this->createMock(Store::class);
         $currencyMock = $this->createMock(Currency::class);
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
     * Rows of a deleted store view fall back to the default store currency
     */
    public function testPrepareDataSourceWithDeletedStore(): void
    {
        $currencyMock = $this->createMock(Currency::class);
        $currencyMock->expects($this->once())->method('getCurrencyCode')->willReturn('USD');
        $defaultStore = $this->createMock(Store::class);
        $defaultStore->expects($this->once())->method('getBaseCurrency')->willReturn($currencyMock);
        $this->storeManagerMock->expects($this->exactly(2))
            ->method('getStore')
            ->willReturnCallback(
                static function ($storeId) use ($defaultStore) {
                    if ($storeId === Store::DEFAULT_STORE_ID) {
                        return $defaultStore;
                    }
                    throw new NoSuchEntityException(__('The store that was requested wasn\'t found.'));
                }
            );
        $this->currencyMock->expects($this->once())->method('load')->with('USD')->willReturnSelf();
        $this->currencyMock->expects($this->once())
            ->method('format')
            ->with('10.0000', [], false)
            ->willReturn('$10.00');

        $this->model->setData('name', 'base_grand_total');
        $dataSource = $this->model->prepareDataSource(
            ['data' => ['items' => [['base_grand_total' => '10.0000', 'store_id' => '5']]]]
        );
        $this->assertSame('$10.00', $dataSource['data']['items'][0]['base_grand_total']);
    }

    /**
     * Provider for testPrepareDataSource
     *
     * @return array
     */
    public static function prepareDataSourceDataProvider(): array
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
