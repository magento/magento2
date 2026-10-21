<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\ResourceModel\Product;

use Magento\Catalog\Model\ResourceModel\Product;
use Magento\CatalogImportExport\Model\ResourceModel\Product\ExportCollection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\TestCase;

class ExportCollectionTest extends TestCase
{
    public function testCandidateSelectorsDeduplicateSqlButNotDifferentBindings(): void
    {
        $adapter = $this->createMock(AdapterInterface::class);
        $entity = $this->createMock(Product::class);
        $entity->method('getLinkField')->willReturn('row_id');
        $entity->method('getIdFieldName')->willReturn('entity_id');
        $select = $this->createMock(Select::class);
        $select->method('__toString')->willReturn('SELECT DISTINCT row_id, entity_id WHERE scope = :scope');
        $select->method('distinct')->with(true)->willReturnSelf();
        $select->method('columns')->with(['e.row_id', 'e.entity_id'])->willReturnSelf();
        $select->method('order')->with('e.row_id ASC')->willReturnSelf();
        $select->method('limit')->with(2)->willReturnSelf();
        $collection = $this->createPartialMock(
            ExportCollection::class,
            ['_renderFilters', '_getClearSelect', 'getEntity', 'getConnection']
        );
        $collection->method('_getClearSelect')->willReturn($select);
        $collection->method('getEntity')->willReturn($entity);
        $collection->method('getConnection')->willReturn($adapter);
        $adapter->expects($this->exactly(2))->method('fetchPairs')
            ->willReturnCallback(static function (Select $query, array $bindings) use ($select): array {
                self::assertSame($select, $query);
                return $bindings === ['scope' => 1] ? [401 => '7', 510 => '9'] : [620 => '11'];
            });
        $bindings = new \ReflectionProperty(ExportCollection::class, '_bindParams');
        $bindings->setValue($collection, ['scope' => 1]);
        $queriedSelectors = [];

        self::assertSame([401 => 7, 510 => 9], $collection->getExportCandidateIdentities(2, $queriedSelectors));
        self::assertSame([], $collection->getExportCandidateIdentities(2, $queriedSelectors));

        $bindings->setValue($collection, ['scope' => 2]);
        self::assertSame([620 => 11], $collection->getExportCandidateIdentities(2, $queriedSelectors));
    }
}
