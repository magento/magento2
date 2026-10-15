<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\Dto;

use Magento\Framework\Setup\Declaration\Schema\Comparator;
use Magento\Framework\Setup\Declaration\Schema\Dto\Column;
use Magento\Framework\Setup\Declaration\Schema\Dto\Index;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test for index structural element.
 */
class IndexTest extends TestCase
{
    /**
     * @return array
     */
    public static function compareDataProvider(): array
    {
        return [
            'innodb, declared hash, stored btree' => ['innodb', 'hash', 'btree', true],
            'innodb, declared btree, stored btree' => ['innodb', 'btree', 'btree', true],
            'innodb, declared fulltext, stored btree' => ['innodb', 'fulltext', 'btree', false],
            'memory, declared hash, stored hash' => ['memory', 'hash', 'hash', true],
            'memory, declared btree, stored hash' => ['memory', 'btree', 'hash', false],
            'memory, declared hash, stored btree' => ['memory', 'hash', 'btree', false],
        ];
    }

    /**
     * Test that declared and stored index types are compared as they are stored by the table engine.
     *
     * @param string $engine
     * @param string $declaredIndexType
     * @param string $storedIndexType
     * @param bool $isEqual
     */
    #[DataProvider('compareDataProvider')]
    public function testCompareIndexTypes(
        string $engine,
        string $declaredIndexType,
        string $storedIndexType,
        bool $isEqual
    ): void {
        $table = new Table('table', 'table', 'table', 'default', $engine, 'utf8mb4', 'utf8mb4_general_ci', '');
        $column = new Column('column', 'int', $table);

        $this->assertSame(
            $isEqual,
            (new Comparator())->compare(
                new Index('IDX_COLUMN', 'index', $table, [$column], $declaredIndexType, 'IDX_COLUMN'),
                new Index('IDX_COLUMN', 'index', $table, [$column], $storedIndexType, 'IDX_COLUMN')
            )
        );
    }
}
