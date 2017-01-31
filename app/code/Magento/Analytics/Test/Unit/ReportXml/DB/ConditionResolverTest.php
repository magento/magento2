<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\ReportXml\DB;

use Magento\Analytics\ReportXml\DB\ConditionResolver;
use Magento\Framework\App\ResourceConnection;
use Magento\Analytics\ReportXml\DB\SelectBuilder;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class ConditionResolverTest
 */
class ConditionResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var ConditionResolver
     */
    private $conditionResolver;

    /**
     * @var SelectBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selectBuilderMock;

    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->selectBuilderMock = $this->getMockBuilder(SelectBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->conditionResolver = new ConditionResolver($this->resourceConnectionMock);
    }

    public function testGetFilter()
    {
        //public function getFilter(SelectBuilder $selectBuilder, $filterConfig, $aliasName, $referencedAlias = null)
        $condition = ["type" => "variable", "_value" => "1", "attribute" => "id", "operator" => "neq"];
        $valueCondition = ["type" => "value", "_value" => "2", "attribute" => "first_name", "operator" => "eq"];
        $identifierCondition = [
            "type" => "identifier",
            "_value" => "3",
            "attribute" => "last_name",
            "operator" => "eq"];
        $filter = [["glue" => "AND", "condition" => [$valueCondition]]];
        $filterConfig = [
            ["glue" => "OR", "condition" => [$condition], 'filter' => $filter],
            ["glue" => "OR", "condition" => [$identifierCondition]],
        ];
        $filtersParts = [];
        $aliasName = 'n';
        $this->selectBuilderMock->expects($this->any())
            ->method('setParams')
            ->with(array_merge([], [$condition['_value']]));

        $this->selectBuilderMock->expects($this->once())
            ->method('getParams')
            ->willReturn([]);

        $this->selectBuilderMock->expects($this->any())
            ->method('getColumns')
            ->willReturn(['price' => new \Zend_Db_Expr("(n.price = 400)")]);

        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->connectionMock->expects($this->any())
            ->method('quote')
            ->willReturn("'John'");

        $this->connectionMock->expects($this->once())
            ->method('quoteIdentifier')
            ->willReturn("'Smith'");
        $result = "(n.id != 1 OR ((n.first_name = 'John'))) AND (n.last_name = 'Smith')";
        $this->assertEquals(
            $this->conditionResolver->getFilter($this->selectBuilderMock, $filterConfig, $aliasName),
            $result
        );
    }
}
