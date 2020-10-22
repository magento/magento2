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
use Magento\Framework\Model\ResourceModel\Db\UpdateEntityRow;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpdateEntityRowTest extends TestCase
{
    /**
     * @var UpdateEntityRow
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $metadataPoolMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManager->getObject(
            UpdateEntityRow::class,
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

        $entityMetadataMock = $this->getMockBuilder(EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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
