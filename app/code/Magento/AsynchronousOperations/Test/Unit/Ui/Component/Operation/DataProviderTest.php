<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Test\Unit\Ui\Component\Operation;

use Magento\AsynchronousOperations\Model\BulkSummary;
use Magento\AsynchronousOperations\Model\Operation\Details;
use Magento\AsynchronousOperations\Model\ResourceModel\Bulk\Collection;
use Magento\AsynchronousOperations\Model\ResourceModel\Bulk\CollectionFactory;
use Magento\AsynchronousOperations\Ui\Component\Operation\DataProvider;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataProviderTest extends TestCase
{
    /**
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * @var MockObject
     */
    private $bulkCollectionFactoryMock;

    /**
     * @var MockObject
     */
    private $bulkCollectionMock;

    /**
     * @var MockObject
     */
    private $operationDetailsMock;

    /**
     * @var MockObject
     */
    private $requestMock;

    /**
     * @var MockObject
     */
    private $bulkMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->bulkCollectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->bulkCollectionMock = $this->createMock(
            Collection::class
        );
        $this->operationDetailsMock = $this->createMock(Details::class);
        $this->bulkMock = $this->createMock(BulkSummary::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);

        $this->bulkCollectionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->bulkCollectionMock);

        $this->dataProvider = $helper->getObject(
            DataProvider::class,
            [
                'name' => 'test-name',
                'bulkCollectionFactory' => $this->bulkCollectionFactoryMock,
                'operationDetails' => $this->operationDetailsMock,
                'request' => $this->requestMock
            ]
        );
    }

    public function testGetData()
    {
        $testData = [
            'id' => '1',
            'uuid' => 'bulk-uuid1',
            'user_id' => '2',
            'description' => 'Description'
        ];
        $testOperationData = [
            'operations_total' => 2,
            'operations_successful' => 1,
            'operations_failed' => 2
        ];
        $testSummaryData = [
            'summary' => '2 items selected for mass update, 1 successfully updated, 2 failed to update'
        ];
        $resultData[$testData['id']] = array_merge($testData, $testOperationData, $testSummaryData);

        $this->bulkCollectionMock
            ->expects($this->once())
            ->method('getItems')
            ->willReturn([$this->bulkMock]);
        $this->bulkMock
            ->expects($this->once())
            ->method('getData')
            ->willReturn($testData);
        $this->operationDetailsMock
            ->expects($this->once())
            ->method('getDetails')
            ->with($testData['uuid'])
            ->willReturn($testOperationData);
        $this->bulkMock
            ->expects($this->once())
            ->method('getBulkId')
            ->willReturn($testData['id']);

        $expectedResult = $this->dataProvider->getData();
        $this->assertEquals($resultData, $expectedResult);
    }

    public function testPrepareMeta()
    {
        $resultData['retriable_operations']['arguments']['data']['disabled'] = true;
        $resultData['failed_operations']['arguments']['data']['disabled'] = true;
        $testData = [
            'uuid' => 'bulk-uuid1',
            'failed_retriable' => 0,
            'failed_not_retriable' => 0
        ];

        $this->requestMock
            ->expects($this->once())
            ->method('getParam')
            ->willReturn($testData['uuid']);
        $this->operationDetailsMock
            ->expects($this->once())
            ->method('getDetails')
            ->with($testData['uuid'])
            ->willReturn($testData);

        $expectedResult = $this->dataProvider->prepareMeta([]);
        $this->assertEquals($resultData, $expectedResult);
    }
}
