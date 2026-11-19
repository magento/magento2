<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Model\ResourceModel\Review\Product;

use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select\SelectRenderer;
use Magento\Review\Model\ResourceModel\Review\Product\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class GetResultingIdsTest extends TestCase
{
    #[DataProvider('idsProvider')]
    public function testSelectsReviewIdsWithoutChangingCollection(array $ids, bool $aliases, bool $having): void
    {
        $connection = $this->createMock(Mysql::class);
        $connection->method('quoteInto')->willReturnArgument(0);
        $select = new Select($connection, $this->createMock(SelectRenderer::class));
        $select->from(['e' => 'catalog_product_entity'])
            ->join(['rt' => 'review'], 'rt.entity_pk_value = e.entity_id', ['review_id', 'status_id'])
            ->join(['rdt' => 'review_detail'], 'rdt.review_id = rt.review_id', ['title', 'detail'])
            ->where('rt.status_id = 1')
            ->group('rt.review_id')
            ->order('rt.review_id DESC')
            ->limit(1, 1);
        $nameExpression = new \Zend_Db_Expr('COALESCE(rdt.title, e.sku)');
        if ($aliases) {
            $select->columns(['name' => $nameExpression])
                ->order('name ASC');
        }
        if ($having) {
            $select->having('name IS NOT NULL')->having('title IS NOT NULL');
        }
        $originalParts = [];
        $parts = [
            Select::COLUMNS,
            Select::FROM,
            Select::WHERE,
            Select::GROUP,
            Select::HAVING,
            Select::ORDER,
            Select::LIMIT_COUNT,
            Select::LIMIT_OFFSET
        ];
        foreach ($parts as $part) {
            $originalParts[$part] = $select->getPart($part);
        }
        $expectedColumns = [['rt', 'review_id', null]];
        if ($having) {
            $expectedColumns = [
                ['rt', 'review_id', null],
                ['e', '*', null],
                ['rt', 'status_id', null],
                ['rdt', 'title', null],
                ['rdt', 'detail', null]
            ];
        }
        if ($aliases) {
            $expectedColumns[] = ['e', $nameExpression, 'name'];
        }
        $assertSelect = function (Select $idsSelect) use ($select, $originalParts, $expectedColumns): void {
            self::assertSame($expectedColumns, $idsSelect->getPart(Select::COLUMNS));
            self::assertNotSame($select, $idsSelect);
            self::assertNull($idsSelect->getPart(Select::LIMIT_COUNT));
            self::assertNull($idsSelect->getPart(Select::LIMIT_OFFSET));
            foreach ([Select::FROM, Select::WHERE, Select::GROUP, Select::HAVING, Select::ORDER] as $part) {
                self::assertSame($originalParts[$part], $idsSelect->getPart($part));
            }
        };
        $connection->expects(self::never())->method('fetchAll');
        $connection->expects(self::once())->method('fetchCol')->willReturnCallback(
            function (Select $idsSelect) use ($assertSelect, $ids): array {
                $assertSelect($idsSelect);
                return $ids;
            }
        );
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSelect', 'getConnection'])
            ->getMock();
        $collection->method('getSelect')->willReturn($select);
        $collection->method('getConnection')->willReturn($connection);

        self::assertSame($ids, $collection->getResultingIds());
        foreach ($originalParts as $part => $value) {
            self::assertSame($value, $select->getPart($part));
        }
    }

    public static function idsProvider(): array
    {
        return [
            'sorted IDs' => [['42', '17'], false, false],
            'no matches' => [[], false, false],
            'alias sort' => [['42', '17'], true, false],
            'alias and unaliased HAVING' => [['42', '17'], true, true]
        ];
    }
}
