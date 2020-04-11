<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdvancedSearch\Test\Unit\Block;

use Magento\AdvancedSearch\Block\SearchData;
use Magento\AdvancedSearch\Model\SuggestedQueriesInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Search\Model\Query;
use Magento\Search\Model\QueryFactoryInterface;
use Magento\Search\Model\QueryInterface;
use PHPUnit\Framework\MockObject\MockObject as MockObject;
use PHPUnit\Framework\TestCase;

class SearchDataTest extends TestCase
{
    /** @var  Context|MockObject */
    private $context;

    /**
     * @var QueryFactoryInterface|MockObject
     */
    private $queryFactory;

    /**
     * @var Query|MockObject
     */
    private $searchQuery;

    /**
     * @var SuggestedQueriesInterface|MockObject
     */
    private $dataProvider;

    /**
     * @var SearchData
     */
    private $block;

    protected function setUp(): void
    {
        $this->dataProvider = $this->getMockBuilder(SuggestedQueriesInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItems', 'isResultsCountEnabled'])
            ->getMockForAbstractClass();

        $this->searchQuery = $this->getMockBuilder(QueryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQueryText'])
            ->getMockForAbstractClass();
        $this->queryFactory = $this->getMockBuilder(QueryFactoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $this->queryFactory->expects($this->once())
            ->method('get')
            ->will($this->returnValue($this->searchQuery));
        $this->context = $this->createMock(Context::class);
        $this->block = $this->getMockBuilder(SearchData::class)->setConstructorArgs(
            [
                $this->context,
                $this->dataProvider,
                $this->queryFactory,
                'Test Title',
                [],
            ]
        )
            ->setMethods(['getUrl'])
            ->getMockForAbstractClass();
    }

    public function testGetSuggestions()
    {
        $value = [1, 2, 3, 100500];

        $this->dataProvider->expects($this->once())
            ->method('getItems')
            ->with($this->searchQuery)
            ->will($this->returnValue($value));
        $actualValue = $this->block->getItems();
        $this->assertEquals($value, $actualValue);
    }

    public function testGetLink()
    {
        $searchQuery = 'Some test search query';
        $expectedResult = '?q=Some+test+search+query';
        $actualResult = $this->block->getLink($searchQuery);
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testIsShowResultsCount()
    {
        $value = 'qwertyasdfzxcv';
        $this->dataProvider->expects($this->once())
            ->method('isResultsCountEnabled')
            ->will($this->returnValue($value));
        $this->assertEquals($value, $this->block->isShowResultsCount());
    }
}
