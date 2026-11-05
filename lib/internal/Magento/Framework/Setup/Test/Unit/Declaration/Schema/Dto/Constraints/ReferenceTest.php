<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\Dto\Constraints;

use Magento\Framework\DB\Ddl\Table as DdlTable;
use Magento\Framework\Setup\Declaration\Schema\Comparator;
use Magento\Framework\Setup\Declaration\Schema\Dto\Column;
use Magento\Framework\Setup\Declaration\Schema\Dto\Constraints\Reference;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Compare foreign key actions without changing their declared value.
 */
class ReferenceTest extends TestCase
{
    /**
     * Verify equivalent restrictive actions and distinct cascading actions.
     *
     * @param string $firstAction
     * @param string $secondAction
     * @param bool $equal
     * @return void
     */
    #[DataProvider('deleteActionProvider')]
    public function testComparison(string $firstAction, string $secondAction, bool $equal): void
    {
        $table = $this->createStub(Table::class);
        $table->method('getName')->willReturn('child');
        $table->method('getResource')->willReturn('default');
        $parent = $this->createStub(Table::class);
        $parent->method('getName')->willReturn('parent');
        $column = new Column('parent_id', 'int', $table);
        $parentColumn = new Column('id', 'int', $parent);
        $arguments = ['CHILD_PARENT', Reference::TYPE, $table, 'CHILD_PARENT', $column, $parent, $parentColumn];
        $first = new Reference(...[...$arguments, $firstAction]);
        $second = new Reference(...[...$arguments, $secondAction]);

        self::assertSame($equal, (new Comparator())->compare($first, $second));
        self::assertSame($equal, (new Comparator())->compare($second, $first));
        self::assertSame($firstAction, $first->getOnDelete());
        self::assertSame($secondAction, $second->getOnDelete());
    }

    /**
     * Foreign key delete action pairs.
     *
     * @return array
     */
    public static function deleteActionProvider(): array
    {
        return [
            'restrict versus omitted database action' => [
                DdlTable::ACTION_RESTRICT,
                DdlTable::ACTION_NO_ACTION,
                true,
            ],
            'restrict unchanged' => [DdlTable::ACTION_RESTRICT, DdlTable::ACTION_RESTRICT, true],
            'no action unchanged' => [DdlTable::ACTION_NO_ACTION, DdlTable::ACTION_NO_ACTION, true],
            'cascade unchanged' => ['CASCADE', 'CASCADE', true],
            'restrict versus cascade' => [DdlTable::ACTION_RESTRICT, 'CASCADE', false],
            'restrict versus set null' => [DdlTable::ACTION_RESTRICT, 'SET NULL', false],
            'no action versus cascade' => [DdlTable::ACTION_NO_ACTION, 'CASCADE', false],
        ];
    }
}
