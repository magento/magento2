<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Model\Test\Unit\ResourceModel\Db;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\CreateEntityRow;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for CreateEntityRow class.
 */
class CreateEntityRowTest extends TestCase
{
    /**
     * Subject of testing.
     *
     * @var CreateEntityRow
     */
    protected $subject;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connection;

    /**
     * @var MetadataPool|MockObject
     */
    protected $metadataPool;

    protected function setUp(): void
    {
        $this->connection = $this->getMockForAbstractClass(
            AdapterInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['lastInsertId']
        );

        $this->connection->expects($this->any())
            ->method('lastInsertId')
            ->willReturn(1);

        $metadata = $this->createMock(EntityMetadata::class);

        $metadata->expects($this->any())
            ->method('getLinkField')
            ->willReturn('entity_id');

        $metadata->expects($this->any())
            ->method('getEntityTable')
            ->willReturn('entity_table');

        $metadata->expects($this->any())
            ->method('getEntityConnection')
            ->willReturn($this->connection);

        $metadata->expects($this->any())
            ->method('getIdentifierField')
            ->willReturn('identifier');

        $metadata->expects($this->once())
            ->method('generateIdentifier')
            ->willReturn('100000001');

        $this->metadataPool = $this->createMock(MetadataPool::class);

        $this->metadataPool->expects($this->any())
            ->method('getMetadata')
            ->with('Test\Entity\Type')
            ->willReturn($metadata);

        $this->subject = new CreateEntityRow(
            $this->metadataPool
        );
    }

    /**
     * @param $inputData
     * @param $tableData
     * @param $preparedData
     * @param $finalData
     * @dataProvider executeDataProvider
     */
    public function testExecute($inputData, $tableData, $preparedData, $finalData)
    {
        $this->connection->expects($this->any())
            ->method('describeTable')
            ->with('entity_table')
            ->willReturn($tableData);

        $this->connection->expects($this->once())
            ->method('insert')
            ->with('entity_table', $preparedData);

        $actualData = $this->subject->execute('Test\Entity\Type', $inputData);

        $this->assertEquals($finalData, $actualData);
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        $inputData = [
            'test_field_1' => 'test_value_1',
            'test_field_2' => 100,
            'test_field_3' => 'test_value_2'
        ];

        $tableData = [
            [
                'COLUMN_NAME' => 'TEST_FIELD_1',
                'DEFAULT' => null
            ],
            [
                'COLUMN_NAME' => 'TEST_FIELD_2',
                'DEFAULT' => null
            ],
            [
                'COLUMN_NAME' => 'TEST_FIELD_3',
                'DEFAULT' => 'CURRENT_TIMESTAMP'
            ],
            [
                'COLUMN_NAME' => 'TEST_FIELD_4',
                'DEFAULT' => null
            ]
        ];

        $preparedData = [
            'test_field_1' => 'test_value_1',
            'test_field_2' => 100,
            'test_field_4' => null,
            'identifier' => '100000001'
        ];

        $finalData = [
            'test_field_1' => 'test_value_1',
            'test_field_2' => 100,
            'test_field_3' => 'test_value_2',
            'entity_id' => 1
        ];

        return [
            [$inputData, $tableData, $preparedData, $finalData]
        ];
    }
}
