<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\Test\Unit;

use \Magento\Framework\DB\Adapter\AdapterInterface;
use \Magento\Framework\DB\Select;
use \Magento\Framework\Indexer\BatchProvider;

class BatchProviderTest extends \PHPUnit\Framework\TestCase
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

        $selectMock = $this->createMock(Select::class);
        $adapterMock = $this->createMock(AdapterInterface::class);

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
            [
                100,
                200,
                [
                    ['limit' => 100, 'offset' => 0],
                    ['limit' => 100, 'offset' => 100]
                ]

            ],
            [
                30,
                66,
                [
                    ['limit' => 30, 'offset' => 0],
                    ['limit' => 30, 'offset' => 30],
                    ['limit' => 30, 'offset' => 60]
                ]
            ],
            [
                200,
                50,
                [
                    ['limit' => 200, 'offset' => 0],
                    ['limit' => 200, 'offset' => 50],
                    ['limit' => 200, 'offset' => 100],
                    ['limit' => 200, 'offset' => 150],
                    ['limit' => 200, 'offset' => 200]
                ]
            ],
            [
                100,
                100,
                [
                    ['limit' => 100, 'offset' => 0]
                ]
            ]
        ];
    }

    public function testGetBatchIds()
    {
        $selectMock = $this->createMock(Select::class);
        $adapterMock = $this->createMock(AdapterInterface::class);

        $selectMock->expects($this->once())->method('order')->with('entity_id')->willReturnSelf();
        $selectMock->expects($this->once())->method('limit')->with(100, 0)->willReturnSelf();
        $adapterMock->expects($this->once())->method('fetchCol')->with($selectMock, [])->willReturn([1, 2, 3]);
        $this->assertEquals(
            [1, 2, 3],
            $this->model->getBatchIds($adapterMock, $selectMock, ['limit' => 100, 'offset' => 0])
        );
    }
}
