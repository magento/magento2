<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\BundleImportExport\Test\Unit\Model\Import\Product\Type\Bundle;

use Magento\BundleImportExport\Model\Import\Product\Type\Bundle\RelationsDataSaver;
use Magento\Catalog\Model\ResourceModel\Product\Relation;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class RelationsDataSaverTest
 */
class RelationsDataSaverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RelationsDataSaver
     */
    private $relationsDataSaver;

    /**
     * @var ResourceConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resourceMock;

    /**
     * @var AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $connectionMock;

    /**
     * @var Relation|\PHPUnit\Framework\MockObject\MockObject
     */
    private $productRelationMock;

    protected function setUp(): void
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->resourceMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->productRelationMock = $this->getMockBuilder(Relation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->relationsDataSaver = $helper->getObject(
            RelationsDataSaver::class,
            [
                'resource' => $this->resourceMock,
                'productRelation' => $this->productRelationMock
            ]
        );
    }

    public function testSaveOptions()
    {
        $options = [1, 2];
        $table_name= 'catalog_product_bundle_option';
        $this->resourceMock->expects($this->once())->method('getConnection')->willReturn($this->connectionMock);
        $this->resourceMock->expects($this->once())
            ->method('getTableName')
            ->with('catalog_product_bundle_option')
            ->willReturn($table_name);
        $this->connectionMock->expects($this->once())
            ->method('insertOnDuplicate')
            ->with(
                $table_name,
                $options,
                [
                    'required',
                    'position',
                    'type'
                ]
            );

        $this->relationsDataSaver->saveOptions($options);
    }

    public function testSaveOptionValues()
    {
        $optionsValues = [1, 2];
        $table_name= 'catalog_product_bundle_option_value';

        $this->resourceMock->expects($this->once())->method('getConnection')->willReturn($this->connectionMock);
        $this->resourceMock->expects($this->once())
            ->method('getTableName')
            ->with('catalog_product_bundle_option_value')
            ->willReturn($table_name);
        $this->connectionMock->expects($this->once())
            ->method('insertOnDuplicate')
            ->with(
                $table_name,
                $optionsValues,
                ['title']
            );

        $this->relationsDataSaver->saveOptionValues($optionsValues);
    }

    public function testSaveSelections()
    {
        $selections = [1, 2];
        $table_name= 'catalog_product_bundle_selection';

        $this->resourceMock->expects($this->once())->method('getConnection')->willReturn($this->connectionMock);
        $this->resourceMock->expects($this->once())
            ->method('getTableName')
            ->with('catalog_product_bundle_selection')
            ->willReturn($table_name);
        $this->connectionMock->expects($this->once())
            ->method('insertOnDuplicate')
            ->with(
                $table_name,
                $selections,
                [
                    'selection_id',
                    'product_id',
                    'position',
                    'is_default',
                    'selection_price_type',
                    'selection_price_value',
                    'selection_qty',
                    'selection_can_change_qty'
                ]
            );

        $this->relationsDataSaver->saveSelections($selections);
    }

    public function testSaveProductRelations()
    {
        $parentId = 1;
        $children = [2, 3];

        $this->productRelationMock->expects($this->once())
            ->method('processRelations')
            ->with($parentId, $children);

        $this->relationsDataSaver->saveProductRelations($parentId, $children);
    }
}
