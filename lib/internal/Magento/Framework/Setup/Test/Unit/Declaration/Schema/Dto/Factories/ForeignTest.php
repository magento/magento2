<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\Dto\Factories;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Constraints\Reference;
use Magento\Framework\Setup\Declaration\Schema\Dto\Factories\Foreign;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;
use Magento\Framework\Setup\Declaration\Schema\TableNameResolver;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Test foreign factory.
 */
class ForeignTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adapterMock;

    /**
     * @var TableNameResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tableNameResolver;

    /**
     * @var Foreign
     */
    private $foreignFactory;

    public function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->adapterMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->tableNameResolver = $this->getMockBuilder(TableNameResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->foreignFactory = $this->objectManagerHelper->getObject(
            Foreign::class,
            [
                'objectManager' => $this->objectManagerMock,
                'resourceConnection' => $this->resourceConnectionMock,
                'tableNameResolver' => $this->tableNameResolver,
            ]
        );
    }

    /**
     * @param string $prefix
     * @dataProvider createDataProvider
     */
    public function testCreate(string $prefix)
    {
        $resource = 'default';
        $tableNameWithoutPrefix = 'table_name';
        $tableName = $prefix . $tableNameWithoutPrefix;

        $columnName = 'entity_id';
        $referenceTableName = 'second_table';
        $referenceColumnName = 'website_id';

        $foreignKeyNameWithoutPrefix = 'table_name_field_name';
        $foreignKeyName = $prefix . $foreignKeyNameWithoutPrefix;

        $table = $this->objectManagerHelper->getObject(
            DataObject::class,
            [
                'data' => [
                    'resource' => $resource,
                    'name' => $tableName,
                    'name_without_prefix' => $tableNameWithoutPrefix,
                ],
            ]
        );

        $columnMock = $this->objectManagerHelper->getObject(
            DataObject::class,
            [
                'data' => ['name' => $columnName],
            ]
        );

        $referenceTableMock = $this->objectManagerHelper->getObject(
            DataObject::class,
            [
                'data' => ['name_without_prefix' => $referenceTableName],
            ]
        );

        $referenceColumnMock = $this->objectManagerHelper->getObject(
            DataObject::class,
            [
                'data' => ['name' => $referenceColumnName],
            ]
        );

        $data = [
            'name' => $foreignKeyName,
            'table' => $table,
            'column' => $columnMock,
            'referenceTable' => $referenceTableMock,
            'referenceColumn' => $referenceColumnMock,
        ];

        $expectedData = array_merge(
            $data,
            [
                'onDelete' => Foreign::DEFAULT_ON_DELETE,
                'nameWithoutPrefix' => $foreignKeyNameWithoutPrefix,
            ]
        );

        $this->resourceConnectionMock
            ->method('getTablePrefix')
            ->willReturn($prefix);

        $this->resourceConnectionMock
            ->method('getConnection')
            ->with($resource)
            ->willReturn($this->adapterMock);

        $this->tableNameResolver
            ->method('getNameOfOriginTable')
            ->with($tableNameWithoutPrefix)
            ->willReturn($tableNameWithoutPrefix);

        $this->adapterMock
            ->method('getForeignKeyName')
            ->with($tableNameWithoutPrefix, $columnName, $referenceTableName, $referenceColumnName)
            ->willReturn($foreignKeyNameWithoutPrefix);

        $this->objectManagerMock
            ->expects($this->once())
            ->method('create')
            ->with(Reference::class, $expectedData);

        $this->foreignFactory->create($data);
    }

    /**
     * @return array
     */
    public function createDataProvider(): array
    {
        return [
            'Prefix is defined' => [
                'pref_',
            ],
            'Prefix is not defined' => [
                '',
            ],
        ];
    }
}
