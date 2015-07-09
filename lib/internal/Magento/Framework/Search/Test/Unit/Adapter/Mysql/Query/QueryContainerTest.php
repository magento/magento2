<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Test\Unit\Adapter\Mysql\Query;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Search\Adapter\Mysql\Query\QueryContainer;
use Magento\Framework\Search\Request\Query\Bool;

class QueryContainerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject */
    private $select;

    /** @var \Magento\Framework\Search\Adapter\Mysql\ScoreBuilder|\PHPUnit_Framework_MockObject_MockObject */
    private $scoreBuilder;

    /** @var \Magento\Framework\Search\Adapter\Mysql\ScoreBuilderFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $scoreBuilderFactory;

    /** @var \Magento\Framework\Search\Request\QueryInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $query;

    /** @var \Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match|\PHPUnit_Framework_MockObject_MockObject */
    private $matchBuilder;

    /** @var \Magento\Framework\Search\Adapter\Mysql\IndexBuilderInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $indexBuilder;

    /** @var \Magento\Framework\Search\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $request;

    /** @var \Magento\Framework\Search\Adapter\Mysql\Query\QueryContainer */
    private $queryContainer;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->select = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->getMock();

        $this->scoreBuilder = $this->getMockBuilder('Magento\Framework\Search\Adapter\Mysql\ScoreBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->scoreBuilderFactory = $this->getMockBuilder('Magento\Framework\Search\Adapter\Mysql\ScoreBuilderFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->scoreBuilderFactory->expects($this->any())->method('create')->willReturn($this->scoreBuilder);

        $this->query = $this->getMockBuilder('Magento\Framework\Search\Request\QueryInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->matchBuilder = $this->getMockBuilder('Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match')
            ->setMethods(['build'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->matchBuilder->expects($this->any())->method('build')->willReturnArgument(1);

        $this->indexBuilder = $this->getMockBuilder('Magento\Framework\Search\Adapter\Mysql\IndexBuilderInterface')
            ->setMethods(['build'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder('\Magento\Framework\Search\RequestInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->queryContainer = $helper->getObject(
            'Magento\Framework\Search\Adapter\Mysql\Query\QueryContainer',
            [
                'scoreBuilderFactory' => $this->scoreBuilderFactory,
                'matchBuilder' => $this->matchBuilder,
                'indexBuilder' => $this->indexBuilder,
                'request' => $this->request
            ]
        );
    }

    public function testBuild()
    {

        $this->scoreBuilder->expects($this->once())->method('build')->willReturn('score condition');
        $subSelect = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->getMock();
        $this->indexBuilder->expects($this->once())->method('build')->willReturn($subSelect);
        $subSelect->expects($this->once())->method('columns')->with('score condition');
        $this->request->expects($this->once())->method('getSize')->willReturn(1000);
        $subSelect->expects($this->once())->method('limit')->with(1000);

        $result = $this->queryContainer->addMatchQuery($this->select, $this->query, Bool::QUERY_CONDITION_MUST);
        $this->assertEquals($this->select, $result);
    }

    public function testGetDerivedQueryNames()
    {
        $this->testBuild();
        $expected = [QueryContainer::DERIVED_QUERY_PREFIX . '0'];
        $this->assertEquals($expected, $this->queryContainer->getDerivedQueryNames());
    }

    public function testGetDerivedQueries()
    {
        $this->testBuild();
        $queries = $this->queryContainer->getDerivedQueries();
        $this->assertCount(1, $queries);
        $this->assertEquals($this->select, reset($queries));
    }

    public function testFilters()
    {
        $this->assertEmpty($this->queryContainer->getFilters());
        $this->queryContainer->addFilter('filter');
        $this->assertCount(1, $this->queryContainer->getFilters());
        $this->assertEquals(1, $this->queryContainer->getFiltersCount());
        $this->queryContainer->clearFilters();
        $this->assertCount(0, $this->queryContainer->getFilters());
        $this->assertEquals(1, $this->queryContainer->getFiltersCount());
    }
}
