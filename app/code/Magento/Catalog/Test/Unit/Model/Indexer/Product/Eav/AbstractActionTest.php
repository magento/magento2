<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Eav;

use Magento\Catalog\Model\Indexer\Product\Eav\AbstractAction;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Decimal;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\DecimalFactory;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Source;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\SourceFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractActionTest extends TestCase
{
    /**
     * @var AbstractAction|MockObject
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_eavDecimalFactoryMock;

    /**
     * @var MockObject
     */
    protected $_eavSourceFactoryMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->_eavDecimalFactoryMock = $this->createPartialMock(
            DecimalFactory::class,
            ['create']
        );
        $this->_eavSourceFactoryMock = $this->createPartialMock(
            SourceFactory::class,
            ['create']
        );
        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->_model = $this->getMockForAbstractClass(
            AbstractAction::class,
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
            ->willReturn($expectedIndexers['source']);

        $this->_eavDecimalFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($expectedIndexers['decimal']);

        $this->assertEquals($expectedIndexers, $this->_model->getIndexers());
    }

    public function testGetIndexerWithUnknownTypeThrowsException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Unknown EAV indexer type "unknown_type".');
        $this->_eavSourceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn('return_value');

        $this->_eavDecimalFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn('return_value');

        $this->_model->getIndexer('unknown_type');
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function testGetIndexer()
    {
        $this->_eavSourceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn('source_return_value');

        $this->_eavDecimalFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn('decimal_return_value');

        $this->assertEquals('source_return_value', $this->_model->getIndexer('source'));
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testReindexWithoutArgumentsExecutesReindexAll()
    {
        $eavSource = $this->getMockBuilder(Source::class)
            ->disableOriginalConstructor()
            ->getMock();

        $eavDecimal = $this->getMockBuilder(Decimal::class)
            ->disableOriginalConstructor()
            ->getMock();

        $eavDecimal->expects($this->once())
            ->method('reindexAll');

        $eavSource->expects($this->once())
            ->method('reindexAll');

        $this->_eavSourceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eavSource);

        $this->_eavDecimalFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eavDecimal);

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

        $connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->getMockForAbstractClass();

        $eavSource = $this->getMockBuilder(Source::class)
            ->disableOriginalConstructor()
            ->getMock();

        $eavDecimal = $this->getMockBuilder(Decimal::class)
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
            ->willReturn($eavSource);

        $this->_eavDecimalFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eavDecimal);

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
    public static function reindexEntitiesDataProvider() : array
    {
        return [
            [[4], [], [1, 2, 3]],
            [[3], [4], []],
            [[5], [], []],
        ];
    }
}
