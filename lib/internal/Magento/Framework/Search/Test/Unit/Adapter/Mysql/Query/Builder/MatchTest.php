<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Test\Unit\Adapter\Mysql\Query\Builder;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class MatchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\ScoreBuilder|MockObject
     */
    private $scoreBuilder;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Field\ResolverInterface|MockObject
     */
    private $resolver;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match
     */
    private $match;

    /**
     * @var \Magento\Framework\DB\Helper\Mysql\Fulltext|MockObject
     */
    private $fulltextHelper;

    /**
     * @var \Magento\Framework\Search\Adapter\Preprocessor\PreprocessorInterface|MockObject
     */
    private $preprocessor;

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

        $this->preprocessor = $this->getMockBuilder('Magento\Search\Adapter\Query\Preprocessor\Synonyms')
            ->setMethods(['process'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->match = $helper->getObject(
            'Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match',
            [
                'resolver' => $this->resolver,
                'fulltextHelper' => $this->fulltextHelper,
                'preprocessors' => [$this->preprocessor]
            ]
        );
    }

    public function testBuild()
    {
        /** @var Select|\PHPUnit_Framework_MockObject_MockObject $select */
        $select = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->setMethods(['getMatchQuery', 'match', 'where'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->preprocessor->expects($this->once())
            ->method('process')
            ->with($this->equalTo('some_value '))
            ->will($this->returnValue('some_value '));
        $this->fulltextHelper->expects($this->once())
            ->method('getMatchQuery')
            ->with($this->equalTo(['some_field' => 'some_field']), $this->equalTo('-some_value*'))
            ->will($this->returnValue('matchedQuery'));
        $select->expects($this->once())
            ->method('where')
            ->with('matchedQuery')
            ->willReturnSelf();

        $this->resolver->expects($this->once())
            ->method('resolve')
            ->willReturnCallback(function ($fieldList) {
                $resolvedFields = [];
                foreach ($fieldList as $column) {
                    $field = $this->getMockBuilder('\Magento\Framework\Search\Adapter\Mysql\Field\FieldInterface')
                        ->disableOriginalConstructor()
                        ->getMockForAbstractClass();
                    $field->expects($this->any())
                        ->method('getColumn')
                        ->willReturn($column);
                    $resolvedFields[] = $field;
                }
                return $resolvedFields;
            });

        /** @var \Magento\Framework\Search\Request\Query\Match|\PHPUnit_Framework_MockObject_MockObject $query */
        $query = $this->getMockBuilder('Magento\Framework\Search\Request\Query\Match')
            ->setMethods(['getMatches', 'getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $query->expects($this->once())
            ->method('getValue')
            ->willReturn('some_value ');
        $query->expects($this->once())
            ->method('getMatches')
            ->willReturn([['field' => 'some_field']]);

        $this->scoreBuilder->expects($this->once())
            ->method('addCondition');

        $result = $this->match->build(
            $this->scoreBuilder,
            $select,
            $query,
            BoolExpression::QUERY_CONDITION_NOT,
            [$this->preprocessor]
        );

        $this->assertEquals($select, $result);
    }
}
