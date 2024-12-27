<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Setup\Patch\Data;

use Magento\Catalog\Setup\Patch\Data\UpdateMultiselectAttributesBackendTypes;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use PHPUnit\Framework\TestCase;

class UpdateMultiselectAttributesBackendTypesTest extends TestCase
{
    /**
     * @var ModuleDataSetupInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $dataSetup;

    /**
     * @var EavSetupFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $eavSetupFactory;

    /**
     * @var UpdateMultiselectAttributesBackendTypes
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->dataSetup = $this->createMock(ModuleDataSetupInterface::class);
        $this->eavSetupFactory = $this->createMock(EavSetupFactory::class);
        $this->model = new UpdateMultiselectAttributesBackendTypes($this->dataSetup, $this->eavSetupFactory);
    }

    public function testApply(): void
    {
        $attributeIds = [3, 7];
        $entityTypeId = 4;
        $eavSetup = $this->createMock(EavSetup::class);
        $connection = $this->createMock(AdapterInterface::class);
        $select1 = $this->createMock(Select::class);
        $select2 = $this->createMock(Select::class);
        $select3 = $this->createMock(Select::class);
        $statement = $this->createMock(\Zend_Db_Statement_Interface::class);

        $this->eavSetupFactory->method('create')
            ->willReturn($eavSetup);
        $this->dataSetup->method('getConnection')
            ->willReturn($connection);
        $this->dataSetup->method('getTable')
            ->willReturnArgument(0);
        $eavSetup->method('getEntityTypeId')
            ->willReturn(4);
        $eavSetup->method('updateAttribute')
            ->willReturnCallback(function (...$args) use ($entityTypeId) {
                static $index = 0;
                $expectedArgs = [
                    [$entityTypeId, 3, 'backend_type', 'text'],
                    [$entityTypeId, 7, 'backend_type', 'text']
                ];

                $index++;
                if ($args === $expectedArgs[$index - 1]) {
                    return null;
                }
            });
        $connection->expects($this->exactly(2))
            ->method('select')
            ->willReturnOnConsecutiveCalls($select1, $select2, $select3);
        $connection->method('describeTable')
            ->willReturn(
                [
                    'value_id' => [],
                    'attribute_id' => [],
                    'store_id' => [],
                    'value' => [],
                    'row_id' => [],
                ]
            );
        $connection->method('query')
            ->willReturn($statement);
        $connection->method('fetchAll')
            ->willReturn([]);
        $connection->method('fetchCol')
            ->with($select1)
            ->willReturn($attributeIds);
        $connection->method('insertFromSelect')
            ->with($select3, 'catalog_product_entity_text', ['attribute_id', 'store_id', 'value', 'row_id'])
            ->willReturn('');
        $connection->method('deleteFromSelect')
            ->with($select2, 'catalog_product_entity_varchar')
            ->willReturn('');
        $select1->method('from')
            ->with('eav_attribute', ['attribute_id'])
            ->willReturnSelf();
        $select1->method('where')
            ->willReturnCallback(function (...$args) use ($entityTypeId, $select1) {
                static $index = 0;
                $expectedArgs = [
                    ['entity_type_id = ?', $entityTypeId,null],
                    ['backend_type = ?', 'varchar',null],
                    ['frontend_input = ?', 'multiselect',null]
                ];
                $returnValue = $select1;
                $index++;
                return $args === $expectedArgs[$index - 1] ? $returnValue : null;
            });
        $select2->method('from')
            ->with('catalog_product_entity_varchar')
            ->willReturnSelf();
        $select2->method('where')
            ->with('attribute_id in (?)', $attributeIds)
            ->willReturnSelf();
        $select3->method('from')
            ->with('catalog_product_entity_varchar', ['attribute_id', 'store_id', 'value', 'row_id'])
            ->willReturnSelf();
        $select3->method('where')
            ->with('attribute_id in (?)', $attributeIds)
            ->willReturnSelf();
        $this->model->apply();
    }
}
