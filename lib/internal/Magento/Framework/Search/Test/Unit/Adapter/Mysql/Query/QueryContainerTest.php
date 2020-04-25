<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Test\Unit\Adapter\Mysql\Query;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Query\MatchContainerFactory;
use Magento\Framework\Search\Adapter\Mysql\Query\QueryContainer;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Framework\Search\Request\QueryInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QueryContainerTest extends TestCase
{
    /** @var Select|MockObject */
    private $select;

    /** @var MatchContainerFactory|MockObject */
    private $matchContainerFactory;

    /** @var QueryInterface|MockObject */
    private $requestQuery;

    /** @var QueryContainer */
    private $queryContainer;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->matchContainerFactory = $this->getMockBuilder(
            MatchContainerFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestQuery = $this->getMockBuilder(QueryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->queryContainer = $helper->getObject(
            QueryContainer::class,
            [
                'matchContainerFactory' => $this->matchContainerFactory,
            ]
        );
    }

    public function testBuild()
    {
        $this->matchContainerFactory->expects($this->once())->method('create')
            ->willReturn('asdf');

        $result = $this->queryContainer->addMatchQuery(
            $this->select,
            $this->requestQuery,
            BoolExpression::QUERY_CONDITION_MUST
        );
        $this->assertEquals($this->select, $result);
    }

    public function testGetDerivedQueries()
    {
        $this->matchContainerFactory->expects($this->once())->method('create')
            ->willReturn('asdf');

        $result = $this->queryContainer->addMatchQuery(
            $this->select,
            $this->requestQuery,
            BoolExpression::QUERY_CONDITION_MUST
        );
        $this->assertEquals($this->select, $result);

        $queries = $this->queryContainer->getMatchQueries();
        $this->assertCount(1, $queries);
        $this->assertEquals('asdf', reset($queries));
    }
}
