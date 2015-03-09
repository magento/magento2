<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Test\Unit\Adapter\Mysql\Builder\Query;

use Magento\Framework\DB\Helper\Mysql\Fulltext;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\Query\Bool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class MatchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\ScoreBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scoreBuilder;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Field\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resolver;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match
     */
    private $match;

    /**
     * @var \Magento\Framework\DB\Helper\Mysql\Fulltext|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fulltextHelper;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->scoreBuilder = $this->getMockBuilder('Magento\Framework\Search\Adapter\Mysql\ScoreBuilder')
            ->setMethods(['addCondition'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->resolver = $this->getMockBuilder('Magento\Framework\Search\Adapter\Mysql\Field\ResolverInterface')
            ->setMethods(['resolve'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->fulltextHelper = $this->getMockBuilder('Magento\Framework\DB\Helper\Mysql\Fulltext')
            ->disableOriginalConstructor()
            ->getMock();

        $this->match = $helper->getObject(
            'Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match',
            ['resolver' => $this->resolver, 'fulltextHelper' => $this->fulltextHelper]
        );
    }

    public function testBuildQuery()
    {
        $boost = 3.14;

        /** @var Select|\PHPUnit_Framework_MockObject_MockObject $select */
        $select = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->setMethods(['getMatchQuery', 'match'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->fulltextHelper->expects($this->once())
            ->method('getMatchQuery')
            ->with($this->equalTo(['some_field']), $this->equalTo('-some_value*'))
            ->will($this->returnValue('matchedQuery'));
        $this->fulltextHelper->expects($this->once())
            ->method('match')
            ->withConsecutive([$select, ['some_field'], '-some_value*', true, Fulltext::FULLTEXT_MODE_BOOLEAN])
            ->willReturn($select);

        $this->resolver->expects($this->once())
            ->method('resolve')
            ->willReturnArgument(0);

        /** @var \Magento\Framework\Search\Request\Query\Match|\PHPUnit_Framework_MockObject_MockObject $query */
        $query = $this->getMockBuilder('Magento\Framework\Search\Request\Query\Match')
            ->setMethods(['getMatches', 'getValue', 'getBoost'])
            ->disableOriginalConstructor()
            ->getMock();
        $query->expects($this->once())
            ->method('getValue')
            ->willReturn('some_value ');
        $query->expects($this->once())
            ->method('getBoost')
            ->willReturn($boost);
        $query->expects($this->once())
            ->method('getMatches')
            ->willReturn([['field' => 'some_field']]);

        $this->scoreBuilder->expects($this->once())
            ->method('addCondition');

        $result = $this->match->build($this->scoreBuilder, $select, $query, Bool::QUERY_CONDITION_NOT);

        $this->assertEquals($select, $result);
    }
}
