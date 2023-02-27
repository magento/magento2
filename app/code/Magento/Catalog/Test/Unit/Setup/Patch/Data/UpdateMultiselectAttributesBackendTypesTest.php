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
use Magento\Framework\DB\Query\BatchIteratorInterface;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\Select;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpdateMultiselectAttributesBackendTypesTest extends TestCase
{
    /**
     * @var ModuleDataSetupInterface|MockObject
     */
    private $dataSetup;

    /**
     * @var EavSetupFactory|MockObject
     */
    private $eavSetupFactory;

    /**
     * @var Generator|MockObject
     */
    private $batchQueryGenerator;

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
        $this->batchQueryGenerator = $this->createMock(Generator::class);
        $this->model = new UpdateMultiselectAttributesBackendTypes(
            $this->dataSetup,
            $this->eavSetupFactory,
            $this->batchQueryGenerator
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return void
     */
    public function testApply(): void
    {
        $attributeIds = [3, 7];
        $entityTypeId = 4;
        $eavSetup = $this->createMock(EavSetup::class);
        $connection = $this->createMock(AdapterInterface::class);
        $selectAttributes = $this->createMock(Select::class);
        $selectAttributesValues = $this->createMock(Select::class);
        $selectForInsert1 = $this->createMock(Select::class);
        $selectForInsert2 = $this->createMock(Select::class);
        $selectForDelete1 = $this->createMock(Select::class);
        $selectForDelete2 = $this->createMock(Select::class);
        $batchIterator = $this->getMockForAbstractClass(BatchIteratorInterface::class);
        $batchIterator->method('current')
            ->willReturnOnConsecutiveCalls($selectForInsert1, $selectForInsert2);
        $batchIterator->method('valid')
            ->willReturnOnConsecutiveCalls(true, true, false);
        $this->eavSetupFactory->expects($this->once())
            ->method('create')
            ->willReturn($eavSetup);
        $this->dataSetup->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);
        $this->dataSetup->method('getTable')
            ->willReturnArgument(0);
        $this->batchQueryGenerator->expects($this->once())
            ->method('generate')
            ->willReturn($batchIterator);
        $eavSetup->method('getEntityTypeId')
            ->willReturn(4);
        $eavSetup->expects($this->exactly(2))
            ->method('updateAttribute')
            ->withConsecutive(
                [$entityTypeId, 3, 'backend_type', 'text'],
                [$entityTypeId, 7, 'backend_type', 'text']
            );
        $connection->expects($this->exactly(4))
            ->method('select')
            ->willReturnOnConsecutiveCalls(
                $selectAttributes,
                $selectAttributesValues,
                $selectForDelete1,
                $selectForDelete2
            );
        $connection->expects($this->once())
            ->method('describeTable')
            ->willReturn(
                [
                    'value_id' => [],
                    'attribute_id' => [],
                    'store_id' => [],
                    'value' => [],
                    'row_id' => [],
                ]
            );
        $connection->expects($this->once())
            ->method('fetchCol')
            ->with($selectAttributes)
            ->willReturn($attributeIds);
        $connection->expects($this->exactly(2))
            ->method('insertFromSelect')
            ->withConsecutive(
                [$selectForInsert1, 'catalog_product_entity_text', ['attribute_id', 'store_id', 'value', 'row_id']],
                [$selectForInsert2, 'catalog_product_entity_text', ['attribute_id', 'store_id', 'value', 'row_id']],
            )
            ->willReturn('');
        $connection->expects($this->exactly(2))
            ->method('delete')
            ->withConsecutive(
                ['catalog_product_entity_varchar', 'value_id IN (select1)'],
                ['catalog_product_entity_varchar', 'value_id IN (select2)'],
            )
            ->willReturn('');
        $selectAttributes->expects($this->once())
            ->method('from')
            ->with('eav_attribute', ['attribute_id'])
            ->willReturnSelf();
        $selectAttributes->expects($this->exactly(3))
            ->method('where')
            ->withConsecutive(
                ['entity_type_id = ?', $entityTypeId],
                ['backend_type = ?', 'varchar'],
                ['frontend_input = ?', 'multiselect']
            )
            ->willReturnSelf();
        $selectForInsert1->expects($this->exactly(2))
            ->method('reset')
            ->with(Select::COLUMNS)
            ->willReturnSelf();
        $selectForInsert1->expects($this->exactly(2))
            ->method('columns')
            ->withConsecutive(
                [['attribute_id', 'store_id', 'value', 'row_id']],
                ['value_id']
            )
            ->willReturnSelf();
        $selectForInsert2->expects($this->exactly(2))
            ->method('reset')
            ->with(Select::COLUMNS)
            ->willReturnSelf();
        $selectForInsert2->expects($this->exactly(2))
            ->method('columns')
            ->withConsecutive(
                [['attribute_id', 'store_id', 'value', 'row_id']],
                ['value_id']
            )
            ->willReturnSelf();
        $selectForDelete1->expects($this->once())
            ->method('from')
            ->with($selectForInsert1, 'value_id')
            ->willReturnSelf();
        $selectForDelete1->expects($this->once())
            ->method('assemble')
            ->willReturn('select1');
        $selectForDelete2->expects($this->once())
            ->method('from')
            ->with($selectForInsert2, 'value_id')
            ->willReturnSelf();
        $selectForDelete2->expects($this->once())
            ->method('assemble')
            ->willReturn('select2');
        $selectAttributesValues->expects($this->once())
            ->method('from')
            ->with('catalog_product_entity_varchar', '*')
            ->willReturnSelf();
        $selectAttributesValues->expects($this->once())
            ->method('where')
            ->with('attribute_id in (?)', $attributeIds)
            ->willReturnSelf();
        $this->model->apply();
    }
}
