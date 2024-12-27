<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdvancedSearch\Test\Unit\Block;

use Magento\AdvancedSearch\Block\SearchData;
use Magento\AdvancedSearch\Model\SuggestedQueriesInterface;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Search\Model\QueryFactoryInterface;
use Magento\Search\Model\QueryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\AdvancedSearch\Block\SearchData
 */
class SearchDataTest extends TestCase
{
    /**
     * Testable Object
     *
     * @var SearchData
     */
    private $block;

    /**
     * @var TemplateContext|MockObject
     */
    private $contextMock;

    /**
     * @var QueryFactoryInterface|MockObject
     */
    private $queryFactoryMock;

    /**
     * @var QueryInterface|MockObject
     */
    private $searchQueryMock;

    /**
     * @var SuggestedQueriesInterface|MockObject
     */
    private $dataProvider;

    protected function setUp(): void
    {
        $this->dataProvider = $this->getMockBuilder(SuggestedQueriesInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getItems', 'isResultsCountEnabled'])
            ->getMockForAbstractClass();

        $this->searchQueryMock = $this->getMockBuilder(QueryInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getQueryText'])
            ->getMockForAbstractClass();
        $this->queryFactoryMock = $this->getMockBuilder(QueryFactoryInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMockForAbstractClass();
        $this->queryFactoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->searchQueryMock);
        $this->contextMock = $this->getMockBuilder(TemplateContext::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->block = $this->getMockBuilder(SearchData::class)
            ->setConstructorArgs(
                [
                    $this->contextMock,
                    $this->dataProvider,
                    $this->queryFactoryMock,
                    'Test Title',
                    [],
                ]
            )
            ->onlyMethods(['getUrl'])
            ->getMockForAbstractClass();
    }

    public function testGetSuggestions(): void
    {
        $value = [1, 2, 3, 100500];

        $this->dataProvider->expects($this->once())
            ->method('getItems')
            ->with($this->searchQueryMock)
            ->willReturn($value);
        $actualValue = $this->block->getItems();
        $this->assertEquals($value, $actualValue);
    }

    public function testGetLink(): void
    {
        $searchQueryMock = 'Some test search query';
        $expectedResult = '?q=Some+test+search+query';
        $actualResult = $this->block->getLink($searchQueryMock);
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testIsShowResultsCount(): void
    {
        $value = 'qwertyasdfzxcv';
        $this->dataProvider->expects($this->once())
            ->method('isResultsCountEnabled')
            ->willReturn($value);
        $this->assertEquals($value, $this->block->isShowResultsCount());
    }
}
