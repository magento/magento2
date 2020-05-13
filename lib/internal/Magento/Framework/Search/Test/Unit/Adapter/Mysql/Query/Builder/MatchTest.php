<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Test\Unit\Adapter\Mysql\Query\Builder;

use Magento\Framework\DB\Helper\Mysql\Fulltext;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Field\FieldInterface;
use Magento\Framework\Search\Adapter\Mysql\Field\ResolverInterface;
use Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match;
use Magento\Framework\Search\Adapter\Mysql\ScoreBuilder;
use Magento\Framework\Search\Adapter\Preprocessor\PreprocessorInterface;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Search\Adapter\Query\Preprocessor\Synonyms;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MatchTest extends TestCase
{
    /**
     * @var ScoreBuilder|MockObject
     */
    private $scoreBuilder;

    /**
     * @var ResolverInterface|MockObject
     */
    private $resolver;

    /**
     * @var Match
     */
    private $match;

    /**
     * @var Fulltext|MockObject
     */
    private $fulltextHelper;

    /**
     * @var PreprocessorInterface|MockObject
     */
    private $preprocessor;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->scoreBuilder = $this->getMockBuilder(ScoreBuilder::class)
            ->setMethods(['addCondition'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->resolver = $this->getMockBuilder(ResolverInterface::class)
            ->setMethods(['resolve'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->fulltextHelper = $this->getMockBuilder(Fulltext::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->preprocessor = $this->getMockBuilder(Synonyms::class)
            ->setMethods(['process'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->match = $helper->getObject(
            Match::class,
            [
                'resolver' => $this->resolver,
                'fulltextHelper' => $this->fulltextHelper,
                'preprocessors' => [$this->preprocessor]
            ]
        );
    }

    public function testBuild()
    {
        /** @var Select|MockObject $select */
        $select = $this->getMockBuilder(Select::class)
            ->setMethods(['getMatchQuery', 'match', 'where'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->preprocessor->expects($this->once())
            ->method('process')
            ->with('some_value ')
            ->willReturn('some_value ');
        $this->fulltextHelper->expects($this->once())
            ->method('getMatchQuery')
            ->with(['some_field' => 'some_field'], '-some_value*')
            ->willReturn('matchedQuery');
        $select->expects($this->once())
            ->method('where')
            ->with('matchedQuery')
            ->willReturnSelf();

        $this->resolver->expects($this->once())
            ->method('resolve')
            ->willReturnCallback(function ($fieldList) {
                $resolvedFields = [];
                foreach ($fieldList as $column) {
                    $field = $this->getMockBuilder(FieldInterface::class)
                        ->disableOriginalConstructor()
                        ->getMockForAbstractClass();
                    $field->expects($this->any())
                        ->method('getColumn')
                        ->willReturn($column);
                    $resolvedFields[] = $field;
                }
                return $resolvedFields;
            });

        /** @var \Magento\Framework\Search\Request\Query\Match|MockObject $query */
        $query = $this->getMockBuilder(\Magento\Framework\Search\Request\Query\Match::class)
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
