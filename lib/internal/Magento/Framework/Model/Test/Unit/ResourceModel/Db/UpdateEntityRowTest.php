<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Test\Unit\ResourceModel\Db;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class UpdateEntityRowTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\UpdateEntityRow
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $metadataPoolMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->metadataPoolMock = $this->getMockBuilder(\Magento\Framework\EntityManager\MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManager->getObject(
            \Magento\Framework\Model\ResourceModel\Db\UpdateEntityRow::class,
            ['metadataPool' => $this->metadataPoolMock]
        );
    }

    public function testExecute()
    {
        $entityType = 'Test_Entity';
        $entityTable = 'test_table_1';
        $linkField = 'test_table_2';
        $describeTable = [
            [
                'DEFAULT' => 'CURRENT_TIMESTAMP'
            ],
            [
                'DEFAULT' => 'NOT_CURRENT_TIMESTAMP',
                'IDENTITY' => false,
                'COLUMN_NAME' => 'test_column_name'
            ]
        ];
        $data = [$linkField => $linkField, 'test_column_name' => 'test_column_name'];
        $output['test_column_name'] = 'test_column_name';
        $expectedResult = true;

        $entityMetadataMock = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connectionMock->expects($this->once())
            ->method('describeTable')
            ->willReturn($describeTable);
        $connectionMock->expects($this->once())
            ->method('update')
            ->with($entityTable, $output, [$linkField . ' = ?' => $data[$linkField]])
            ->willReturn($expectedResult);

        $entityMetadataMock->expects($this->any())
            ->method('getEntityConnection')
            ->willReturn($connectionMock);
        $entityMetadataMock->expects($this->any())
            ->method('getEntityTable')
            ->willReturn($entityTable);
        $entityMetadataMock->expects($this->any())
            ->method('getLinkField')
            ->willReturn($linkField);

        $this->metadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->with($entityType)
            ->willReturn($entityMetadataMock);
        $result = $this->model->execute($entityType, $data);
        $this->assertEquals($expectedResult, $result);
    }
}
