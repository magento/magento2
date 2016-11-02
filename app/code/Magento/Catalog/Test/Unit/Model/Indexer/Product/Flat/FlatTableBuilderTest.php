<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Flat;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Class FlatTableBuilderTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FlatTableBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Helper\Product\Flat\Indexer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $flatIndexerMock;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\TableDataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tableDataMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPoolMock;

    /**
     * @var \Magento\Framework\EntityManager\EntityMetadataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataMock;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\FlatTableBuilder
     */
    private $flatTableBuilder;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->flatIndexerMock = $this->getMockBuilder(\Magento\Catalog\Helper\Product\Flat\Indexer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->tableDataMock = $this->getMockBuilder(
            \Magento\Catalog\Model\Indexer\Product\Flat\TableDataInterface::class
        )->disableOriginalConstructor()->getMockForAbstractClass();
        $this->connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->metadataPoolMock = $this->getMockBuilder(\Magento\Framework\EntityManager\MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataMock = $this->getMockBuilder(
            \Magento\Framework\EntityManager\EntityMetadataInterface::class
        )->disableOriginalConstructor()->getMockForAbstractClass();
        $this->metadataMock->expects($this->any())->method('getLinkField')->willReturn('entity_id');

        $this->flatTableBuilder = $objectManagerHelper->getObject(
            \Magento\Catalog\Model\Indexer\Product\Flat\FlatTableBuilder::class,
            [
                'productIndexerHelper' => $this->flatIndexerMock,
                'resource' => $this->resourceMock,
                'config' => $this->scopeConfigMock,
                'storeManager' => $this->storeManagerMock,
                'tableData' => $this->tableDataMock,
                '_connection' => $this->connectionMock
            ]
        );
        $objectManagerHelper->setBackwardCompatibleProperty(
            $this->flatTableBuilder,
            'metadataPool',
            $this->metadataPoolMock
        );
    }

    public function testBuild()
    {
        $storeId = 1;
        $changedIds = [];
        $valueFieldSuffix = '_value';
        $tableDropSuffix = '';
        $fillTmpTables = true;
        $tableName = 'catalog_product_entity';
        $attributeTable = 'catalog_product_entity_int';
        $temporaryTableName = 'catalog_product_entity_int_tmp_indexer';
        $temporaryValueTableName = 'catalog_product_entity_int_tmp_indexer_value';
        $linkField = 'entity_id';
        $statusId = 22;
        $eavCustomField = 'space_weight';
        $eavCustomValueField = $eavCustomField . $valueFieldSuffix;
        $this->flatIndexerMock->expects($this->once())->method('getAttributes')->willReturn([]);
        $this->flatIndexerMock->expects($this->exactly(3))->method('getFlatColumns')
            ->willReturnOnConsecutiveCalls(
                [],
                [$eavCustomValueField => []],
                [$eavCustomValueField => []]
            );
        $this->flatIndexerMock->expects($this->once())->method('getFlatIndexes')->willReturn([]);
        $statusAttributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eavCustomAttributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->flatIndexerMock->expects($this->once())->method('getTablesStructure')
            ->willReturn(
                [
                    'catalog_product_entity' => [
                        $linkField => $statusAttributeMock
                    ],
                    'catalog_product_entity_int' => [
                        $linkField => $statusAttributeMock,
                        $eavCustomField => $eavCustomAttributeMock
                    ]
                ]
            );
        $this->flatIndexerMock->expects($this->atLeastOnce())->method('getTable')
            ->withConsecutive(
                [$tableName],
                ['catalog_product_website']
            )
            ->willReturnOnConsecutiveCalls(
                $tableName,
                'catalog_product_website'
            );
        $this->flatIndexerMock->expects($this->once())->method('getAttribute')
            ->with('status')
            ->willReturn($statusAttributeMock);
        $backendMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend::class)
            ->disableOriginalConstructor()
            ->getMock();
        $backendMock->expects($this->atLeastOnce())->method('getTable')->willReturn($attributeTable);
        $statusAttributeMock->expects($this->atLeastOnce())->method('getBackend')->willReturn(
            $backendMock
        );
        $eavCustomAttributeMock->expects($this->atLeastOnce())->method('getBackend')->willReturn(
            $backendMock
        );
        $statusAttributeMock->expects($this->atLeastOnce())->method('getId')->willReturn($statusId);
        $tableMock = $this->getMockBuilder(\Magento\Framework\DB\Ddl\Table::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionMock->expects($this->any())->method('newTable')->willReturn($tableMock);
        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionMock->expects($this->atLeastOnce())->method('select')->willReturn($selectMock);
        $selectMock->expects($this->once())->method('from')->with(
            ['et' => 'catalog_product_entity_tmp_indexer'],
            [$linkField, 'type_id', 'attribute_set_id']
        )->willReturnSelf();
        $selectMock->expects($this->atLeastOnce())->method('joinInner')->willReturnSelf();
        $selectMock->expects($this->exactly(3))->method('joinLeft')
            ->withConsecutive(
                [
                    ['dstatus' => $attributeTable],
                    sprintf(
                        'e.%s = dstatus.%s AND dstatus.store_id = %s AND dstatus.attribute_id = %s',
                        $linkField,
                        $linkField,
                        $storeId,
                        $statusId
                    ),
                    []
                ],
                [
                    $temporaryTableName,
                    "e.{$linkField} = {$temporaryTableName}.{$linkField}",
                    [$linkField, $eavCustomField]
                ],
                [
                    $temporaryValueTableName,
                    "e.{$linkField} = {$temporaryValueTableName}.{$linkField}",
                    [$eavCustomValueField]
                ]
            )->willReturnSelf();
        $this->metadataPoolMock->expects($this->atLeastOnce())->method('getMetadata')->with(ProductInterface::class)
            ->willReturn($this->metadataMock);
        $storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManagerMock->expects($this->once())->method('getStore')->with($storeId)->willReturn($storeMock);
        $this->flatTableBuilder->build($storeId, $changedIds, $valueFieldSuffix, $tableDropSuffix, $fillTmpTables);
    }
}
