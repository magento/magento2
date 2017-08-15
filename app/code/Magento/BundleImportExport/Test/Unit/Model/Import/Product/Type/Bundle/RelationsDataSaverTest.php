<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\BundleImportExport\Test\Unit\Model\Import\Product\Type\Bundle;

use Magento\BundleImportExport\Model\Import\Product\Type\Bundle\RelationsDataSaver;
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
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->resourceMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock->expects($this->once())->method('getConnection')->willReturn($this->connectionMock);

        $this->relationsDataSaver = $helper->getObject(
            RelationsDataSaver::class,
            [
                'resource' => $this->resourceMock
            ]
        );
    }

    public function testSaveOptions()
    {
        $options = [1, 2];
        $table_name= 'catalog_product_bundle_option';

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
}
