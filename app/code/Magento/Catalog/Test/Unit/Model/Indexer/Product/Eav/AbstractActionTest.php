<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Eav;

class AbstractActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\AbstractAction|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eavDecimalFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eavSourceFactoryMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->_eavDecimalFactoryMock = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\DecimalFactory::class,
            ['create']
        );
        $this->_eavSourceFactoryMock = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\SourceFactory::class,
            ['create']
        );
        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->_model = $this->getMockForAbstractClass(
            \Magento\Catalog\Model\Indexer\Product\Eav\AbstractAction::class,
            [
                $this->_eavDecimalFactoryMock,
                $this->_eavSourceFactoryMock,
                $this->scopeConfig
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetIndexers()
    {
        $expectedIndexers = [
            'source' => 'source_instance',
            'decimal' => 'decimal_instance',
        ];

        $this->_eavSourceFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($expectedIndexers['source']));

        $this->_eavDecimalFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($expectedIndexers['decimal']));

        $this->assertEquals($expectedIndexers, $this->_model->getIndexers());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Unknown EAV indexer type "unknown_type".
     */
    public function testGetIndexerWithUnknownTypeThrowsException()
    {
        $this->_eavSourceFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue('return_value'));

        $this->_eavDecimalFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue('return_value'));

        $this->_model->getIndexer('unknown_type');
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testGetIndexer()
    {
        $this->_eavSourceFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue('source_return_value'));

        $this->_eavDecimalFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue('decimal_return_value'));

        $this->assertEquals('source_return_value', $this->_model->getIndexer('source'));
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testReindexWithoutArgumentsExecutesReindexAll()
    {
        $eavSource = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Source::class)
            ->disableOriginalConstructor()
            ->getMock();

        $eavDecimal = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Decimal::class)
            ->disableOriginalConstructor()
            ->getMock();

        $eavDecimal->expects($this->once())
            ->method('reindexAll');

        $eavSource->expects($this->once())
            ->method('reindexAll');

        $this->_eavSourceFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($eavSource));

        $this->_eavDecimalFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($eavDecimal));

        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->willReturn(1);

        $this->_model->reindex();
    }

    /**
     * @param array $ids
     * @param array $parentIds
     * @param array $childIds
     * @return void
     * @dataProvider reindexEntitiesDataProvider
     */
    public function testReindexWithNotNullArgumentExecutesReindexEntities(
        array $ids,
        array $parentIds,
        array $childIds
    ) : void {
        $reindexIds = array_unique(array_merge($ids, $parentIds, $childIds));

        $connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->getMockForAbstractClass();

        $eavSource = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Source::class)
            ->disableOriginalConstructor()
            ->getMock();

        $eavDecimal = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Decimal::class)
            ->disableOriginalConstructor()
            ->getMock();

        $eavSource->expects($this->once())
            ->method('getRelationsByChild')
            ->with($ids)
            ->willReturn($parentIds);
        $eavSource->expects($this->once())
            ->method('getRelationsByParent')
            ->with(array_unique(array_merge($parentIds, $ids)))
            ->willReturn($childIds);

        $eavDecimal->expects($this->once())
            ->method('getRelationsByChild')
            ->with($reindexIds)
            ->willReturn($parentIds);
        $eavDecimal->expects($this->once())
            ->method('getRelationsByParent')
            ->with(array_unique(array_merge($parentIds, $reindexIds)))
            ->willReturn($childIds);

        $eavSource->expects($this->once())->method('getConnection')->willReturn($connectionMock);
        $eavDecimal->expects($this->once())->method('getConnection')->willReturn($connectionMock);
        $eavDecimal->expects($this->once())
            ->method('reindexEntities')
            ->with($reindexIds);

        $eavSource->expects($this->once())
            ->method('reindexEntities')
            ->with($reindexIds);

        $this->_eavSourceFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($eavSource));

        $this->_eavDecimalFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($eavDecimal));

        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->willReturn(1);

        $this->_model->reindex($ids);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testReindexWithDisabledEavIndexer()
    {
        $this->scopeConfig->expects($this->once())->method('getValue')->willReturn(0);
        $this->_eavSourceFactoryMock->expects($this->never())->method('create');
        $this->_eavDecimalFactoryMock->expects($this->never())->method('create');
        $this->_model->reindex();
    }

    /**
     * @return array
     */
    public function reindexEntitiesDataProvider() : array
    {
        return [
            [[4], [], [1, 2, 3]],
            [[3], [4], []],
            [[5], [], []],
        ];
    }
}
