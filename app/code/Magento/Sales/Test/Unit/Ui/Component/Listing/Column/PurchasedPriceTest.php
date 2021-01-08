<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Ui\Component\Listing\Column;

use Magento\Directory\Model\Currency;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Ui\Component\Listing\Column\Price;
use Magento\Sales\Ui\Component\Listing\Column\PurchasedPrice;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Sales\Ui\Component\Listing\Column\PurchasedPrice
 */
class PurchasedPriceTest extends TestCase
{
    /**
     * @var Price
     */
    private $model;

    /**
     * @var Currency|MockObject
     */
    private $currencyMock;

    /**
     * @var OrderRepositoryInterface|MockObject
     */
    private $orderRepository;

    /**
     * @var SearchCriteria|MockObject
     */
    private $searchCriteriaMock;

    /**
     * @var OrderSearchResultInterface|MockObject
     */
    private $orderSearchResultMock;

    /**
     * @var OrderInterface|MockObject
     */
    private $order;

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
        $this->orderRepository = $this->getMockForAbstractClass(OrderRepositoryInterface::class);
        $this->order = $this->getMockForAbstractClass(OrderInterface::class);
        $this->orderSearchResultMock = $this->getMockForAbstractClass(OrderSearchResultInterface::class);
        $this->searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->willReturnSelf();

        $searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($this->searchCriteriaMock);

        $this->model = $objectManager->getObject(
            PurchasedPrice::class,
            [
                'currency' => $this->currencyMock,
                'context' => $contextMock,
                'orderRepository' => $this->orderRepository,
                'order' => $this->order,
                'searchCriteriaBuilder' => $searchCriteriaBuilderMock,
                'searchCriteria' => $this->searchCriteriaMock,
                'orderSearchResult' => $this->orderSearchResultMock,
            ]
        );
    }

    /**
     * @param array $orderData
     * @param array $dataSource
     * @dataProvider prepareDataSourceDataProvider
     */
    public function testPrepareDataSource(array $orderData,array $dataSource): void
    {
        $oldItemValue = 'oldItemValue';
        $newItemValue = 'newItemValue';

        $this->orderRepository->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteriaMock)
            ->willReturn($this->orderSearchResultMock);

        $this->orderSearchResultMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$this->order]);

        $this->order->expects($this->once())
            ->method('getEntityId')
            ->willReturn($orderData['entity_id']);

        $this->order->expects($this->once())
            ->method('getOrderCurrencyCode')
            ->willReturn($orderData['order_currency_code']);

        $currencyCode = $dataSource['data']['items'][0]['order_currency_code'] ?? $orderData['order_currency_code'];

        $this->currencyMock->expects($this->once())
            ->method('load')
            ->with($currencyCode)
            ->willReturnSelf();

        $this->currencyMock->expects($this->once())
            ->method('format')
            ->with($oldItemValue, [], false)
            ->willReturn($newItemValue);

        $this->model->setData('name', 'item_name');
        $dataSource = $this->model->prepareDataSource($dataSource);
        $this->assertEquals($newItemValue, $dataSource['data']['items'][0]['item_name']);
    }

    /**
     * @return array
     */
    public function prepareDataSourceDataProvider(): array
    {
        return [
            [
                'orderData' => [
                    'entity_id' => 1,
                    'order_currency_code' => 'US',
                ],
                'dataSource' => [
                    'data' => [
                        'items' => [
                            [
                                'item_name' => 'oldItemValue',
                                'order_currency_code' => 'US',
                                'order_id' => 1,
                            ]
                        ]
                    ]
                ]
            ],
            [
                'orderData' => [
                    'entity_id' => 1,
                    'order_currency_code' => 'FR',
                ],
                'dataSource' => [
                    'data' => [
                        'items' => [
                            [
                                'item_name' => 'oldItemValue',
                                'order_id' => 1,
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
