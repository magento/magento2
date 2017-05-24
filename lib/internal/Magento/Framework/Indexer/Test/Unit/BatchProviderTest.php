<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\Test\Unit;

use \Magento\Framework\DB\Adapter\AdapterInterface;
use \Magento\Framework\DB\Select;
use \Magento\Framework\Indexer\BatchProvider;

class BatchProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BatchProvider
     */
    private $model;

    protected function setUp()
    {
        $this->model = new BatchProvider();
    }

    /**
     * @param int $batchSize preferable batch size
     * @param int $maxLinkFieldValue maximum value of the entity identifier in the table
     * @param int $expectedResult list of expected consecutive entity ID ranges (batches)
     *
     * @dataProvider getBatchesDataProvider
     */
    public function testGetBatches($batchSize, $maxLinkFieldValue, $expectedResult)
    {
        $tableName = 'test_table';
        $linkField = 'id';

        $selectMock = $this->getMock(Select::class, [], [], '', false);
        $adapterMock = $this->getMock(AdapterInterface::class);

        $selectMock->expects($this->once())->method('from')->willReturnSelf();
        $adapterMock->expects($this->once())->method('select')->willReturn($selectMock);
        $adapterMock->expects($this->once())->method('fetchOne')->with($selectMock, [])->willReturn($maxLinkFieldValue);
        $batches = $this->model->getBatches($adapterMock, $tableName, $linkField, $batchSize);
        foreach ($batches as $index => $batch) {
            $this->assertEquals($expectedResult[$index], $batch);
        }
    }

    /**
     * @return array
     */
    public function getBatchesDataProvider()
    {
        return [
            [200, 600, [['from' => 1, 'to' => 200], ['from' => 201, 'to' => 400], ['from' => 401, 'to' => 600]]],
            [200, 555, [['from' => 1, 'to' => 200], ['from' => 201, 'to' => 400], ['from' => 401, 'to' => 555]]],
            [200, 10, [['from' => 1, 'to' => 10]]],
            [200, 0, []],
        ];
    }

    public function testGetBatchIds()
    {
        $selectMock = $this->getMock(Select::class, [], [], '', false);
        $adapterMock = $this->getMock(AdapterInterface::class);

        $selectMock->expects($this->once())->method('where')->with('(entity_id BETWEEN 10 AND 100)')->willReturnSelf();
        $selectMock->expects($this->once())->method('limit')->with(91)->willReturnSelf();

        $adapterMock->expects($this->atLeastOnce())->method('quote')->willReturnArgument(0);
        $adapterMock->expects($this->once())->method('fetchCol')->with($selectMock, [])->willReturn([1, 2, 3]);
        $this->assertEquals(
            [1, 2, 3],
            $this->model->getBatchIds($adapterMock, $selectMock, ['from' => 10, 'to' => 100])
        );
    }
}
