<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\ReportXml\DB;

use Magento\Analytics\ReportXml\DB\ConditionResolver;
use Magento\Analytics\ReportXml\DB\SelectBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Sql\Expression;

class ConditionResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ResourceConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var ConditionResolver
     */
    private $conditionResolver;

    /**
     * @var SelectBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $selectBuilderMock;

    /**
     * @var AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $connectionMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->selectBuilderMock = $this->getMockBuilder(SelectBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->conditionResolver = new ConditionResolver($this->resourceConnectionMock);
    }

    public function testGetFilter()
    {
        $condition = ["type" => "variable", "_value" => "1", "attribute" => "id", "operator" => "neq"];
        $valueCondition = ["type" => "value", "_value" => "2", "attribute" => "first_name", "operator" => "eq"];
        $identifierCondition = [
            "type" => "identifier",
            "_value" => "other_field",
            "attribute" => "last_name",
            "operator" => "eq"];
        $filter = [["glue" => "AND", "condition" => [$valueCondition]]];
        $filterConfig = [
            ["glue" => "OR", "condition" => [$condition], 'filter' => $filter],
            ["glue" => "OR", "condition" => [$identifierCondition]],
        ];
        $aliasName = 'n';
        $this->selectBuilderMock->expects($this->any())
            ->method('setParams')
            ->with(array_merge([], [$condition['_value']]));

        $this->selectBuilderMock->expects($this->once())
            ->method('getParams')
            ->willReturn([]);

        $this->selectBuilderMock->expects($this->any())
            ->method('getColumns')
            ->willReturn(['price' => new Expression("(n.price = 400)")]);

        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->connectionMock->expects($this->any())
            ->method('quote')
            ->willReturn("'John'");
        $this->connectionMock->expects($this->exactly(4))
            ->method('quoteIdentifier')
            ->willReturnMap([
                ['n.id', false, '`n`.`id`'],
                ['n.first_name', false, '`n`.`first_name`'],
                ['n.last_name', false, '`n`.`last_name`'],
                ['other_field', false, '`other_field`'],
            ]);

        $result = "(`n`.`id` != 1 OR ((`n`.`first_name` = 'John'))) OR (`n`.`last_name` = `other_field`)";
        $this->assertEquals(
            $result,
            $this->conditionResolver->getFilter($this->selectBuilderMock, $filterConfig, $aliasName)
        );
    }
}
