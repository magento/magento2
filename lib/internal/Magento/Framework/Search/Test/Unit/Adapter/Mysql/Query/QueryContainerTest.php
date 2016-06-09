<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Test\Unit\Adapter\Mysql\Query;

use Magento\Framework\Search\Adapter\Mysql\Query\MatchContainerFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Search\Request\Query\BoolExpression;

class QueryContainerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject */
    private $select;

    /** @var MatchContainerFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $matchContainerFactory;

    /** @var \Magento\Framework\Search\Request\QueryInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $requestQuery;

    /** @var \Magento\Framework\Search\Adapter\Mysql\Query\QueryContainer */
    private $queryContainer;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->select = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->getMock();

        $this->matchContainerFactory = $this->getMockBuilder(
            'Magento\Framework\Search\Adapter\Mysql\Query\MatchContainerFactory'
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestQuery = $this->getMockBuilder('Magento\Framework\Search\Request\QueryInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->queryContainer = $helper->getObject(
            'Magento\Framework\Search\Adapter\Mysql\Query\QueryContainer',
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
