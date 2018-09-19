<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Test\Unit\ResourceModel\Db;

/**
 * Unit test for CreateEntityRow class.
 */
class CreateEntityRowTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Subject of testing.
     *
     * @var \Magento\Framework\Model\ResourceModel\Db\CreateEntityRow
     */
    protected $subject;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataPool;

    protected function setUp()
    {
        $this->connection = $this->getMockForAbstractClass(
            \Magento\Framework\DB\Adapter\AdapterInterface::class,
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

        $metadata = $this->createMock(\Magento\Framework\EntityManager\EntityMetadata::class);

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

        $this->metadataPool = $this->createMock(\Magento\Framework\EntityManager\MetadataPool::class);

        $this->metadataPool->expects($this->any())
            ->method('getMetadata')
            ->with('Test\Entity\Type')
            ->willReturn($metadata);

        $this->subject = new \Magento\Framework\Model\ResourceModel\Db\CreateEntityRow(
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
