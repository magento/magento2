<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Reports\Model\ResourceModel\Helper;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    /**
     * @var ResourceConnection|MockObject
     */
    protected $resourceMock;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connectionMock;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->resourceMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->getMock();

        $this->resourceMock
            ->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->storeManager = $this->createMock(StoreManagerInterface::class);
    }

    /**
     * @return void
     */
    public function testMergeVisitorProductIndex()
    {
        $mainTable = 'mainTable';
        $data = ['dataKey' => 'dataValue'];
        $matchFields = ['matchField'];

        $this->connectionMock
            ->expects($this->once())
            ->method('insertOnDuplicate')
            ->with($mainTable, $data, array_keys($data));

        $helper = new Helper(
            $this->resourceMock,
            $this->storeManager
        );
        $helper->mergeVisitorProductIndex($mainTable, $data, $matchFields);
    }

    /**
     * @param string $type
     * @param array $result
     * @dataProvider typesDataProvider
     * @return void
     */
    public function testUpdateReportRatingPos($type, $result)
    {
        $mainTable = 'mainTable';
        $column = 'column';
        $aggregationTable = 'aggregationTable';

        $store = $this->createMock(StoreInterface::class);
        $store->expects($this->once())->method('getId')->willReturn(1);
        $this->storeManager->expects($this->once())->method('getStores')->willReturn([$store]);
        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectMock
            ->expects($this->any())
            ->method('from')
            ->willReturnSelf();
        $selectMock
            ->expects($this->once())
            ->method('group')
            ->willReturnSelf();
        $selectMock
            ->expects($this->once())
            ->method('order')
            ->willReturnSelf();
        $selectMock
            ->expects($this->once())
            ->method('insertFromSelect')
            ->with($aggregationTable, $result)
            ->willReturnSelf();

        $this->connectionMock
            ->expects($this->any())
            ->method('select')
            ->willReturn($selectMock);

        $helper = new Helper(
            $this->resourceMock,
            $this->storeManager
        );
        $helper->updateReportRatingPos($this->connectionMock, $type, $column, $mainTable, $aggregationTable);
    }

    /**
     * @return array
     */
    public function typesDataProvider()
    {
        $mResult = ['period', 'store_id', 'product_id', 'product_name', 'product_price', 'column', 'rating_pos'];
        $dResult = ['period', 'store_id', 'product_id', 'product_name', 'product_price', 'id', 'column', 'rating_pos'];
        return [
            ['type' => 'year', 'result' => $mResult],
            ['type' => 'month', 'result' => $mResult],
            ['type' => 'day', 'result' => $dResult],
            ['type' => null, 'result' => $mResult]
        ];
    }
}
