<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Mview\ChangelogBatchWalker;

use Magento\Eav\Model\Mview\ChangelogBatchWalker\IdsSelectBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Mview\View\ChangelogInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IdsSelectBuilderTest extends TestCase
{
    public function testMysqlRaisesGroupConcatMaxLenBeforeConcat(): void
    {
        $connection = $this->createMock(Mysql::class);
        $attributeSelect = $this->fluentSelect();
        $batchSelect = $this->fluentSelect();
        $columns = null;
        $batchSelect->method('from')->willReturnCallback(
            function ($table, $selected) use (&$columns, $batchSelect) {
                $columns = [$table, $selected];
                return $batchSelect;
            }
        );
        $connection->method('getTableName')->willReturnArgument(0);
        $connection->method('select')->willReturnOnConsecutiveCalls($attributeSelect, $batchSelect);
        $connection->method('fetchOne')->willReturn('4');
        $connection->method('getGroupConcatSql')
            ->with('attribute_id')
            ->willReturn(new \Zend_Db_Expr('GROUP_CONCAT(attribute_id)'));
        $connection->expects($this->once())
            ->method('query')
            ->with('SET SESSION group_concat_max_len=24');

        $builder = new IdsSelectBuilder($this->resource($connection), ['price_view' => 'catalog_product']);
        $this->assertSame($batchSelect, $builder->build($this->changelog()));
        $this->assertSame('catalog_product_cl', $columns[0]);
        $this->assertSame('GROUP_CONCAT(attribute_id)', (string) $columns[1]['attribute_ids']);
    }

    public function testOtherEnginesSkipGroupConcatSessionVariable(): void
    {
        $connection = $this->createMock(AdapterInterface::class);
        $attributeSelect = $this->fluentSelect();
        $batchSelect = $this->fluentSelect();
        $connection->method('getTableName')->willReturnArgument(0);
        $connection->method('select')->willReturnOnConsecutiveCalls($attributeSelect, $batchSelect);
        $connection->method('fetchOne')->willReturn('4');
        $connection->method('getGroupConcatSql')->willReturn(new \Zend_Db_Expr('GROUP_CONCAT(attribute_id)'));
        $connection->expects($this->never())->method('query');

        $builder = new IdsSelectBuilder($this->resource($connection), ['price_view' => 'catalog_product']);
        $this->assertSame($batchSelect, $builder->build($this->changelog()));
    }

    private function resource(MockObject $connection): ResourceConnection
    {
        $resource = $this->createMock(ResourceConnection::class);
        $resource->method('getConnection')->willReturn($connection);
        $resource->method('getTableName')->willReturnArgument(0);
        return $resource;
    }

    private function changelog(): ChangelogInterface
    {
        $changelog = $this->createMock(ChangelogInterface::class);
        $changelog->method('getName')->willReturn('catalog_product_cl');
        $changelog->method('getColumnName')->willReturn('entity_id');
        $changelog->method('getViewId')->willReturn('price_view');
        return $changelog;
    }

    private function fluentSelect(): Select
    {
        $select = $this->createMock(Select::class);
        $select->method('from')->willReturnSelf();
        $select->method('joinInner')->willReturnSelf();
        $select->method('where')->willReturnSelf();
        $select->method('group')->willReturnSelf();
        return $select;
    }
}
