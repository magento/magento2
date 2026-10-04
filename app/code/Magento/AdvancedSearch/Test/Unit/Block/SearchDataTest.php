<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AdvancedSearch\Test\Unit\Block;

use PHPUnit\Framework\Attributes\CoversClass;
use Magento\AdvancedSearch\Block\Suggestions;
use Magento\AdvancedSearch\Model\SuggestedQueriesInterface;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Search\Model\QueryFactoryInterface;
use Magento\Search\Model\QueryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\AdvancedSearch\Block\SearchData;

#[CoversClass(SearchData::class)]
class SearchDataTest extends TestCase
{
    /**
     * Testable Object
     *
     * @var Suggestions
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
        $this->dataProvider = $this->createMock(SuggestedQueriesInterface::class);

        $this->searchQueryMock = $this->createMock(QueryInterface::class);
        $this->queryFactoryMock = $this->createMock(QueryFactoryInterface::class);
        $this->queryFactoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->searchQueryMock);
        $this->contextMock = $this->getMockBuilder(TemplateContext::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        // Create a real instance of Suggestions (concrete implementation of SearchData) to test actual behavior
        $this->block = $this->getMockBuilder(Suggestions::class)
            ->setConstructorArgs([$this->contextMock, $this->dataProvider, $this->queryFactoryMock, 'title'])
            ->onlyMethods(['getUrl'])
            ->getMock();
        
        // Mock the getUrl method to return a predictable URL
        $this->block->expects($this->any())
            ->method('getUrl')
            ->willReturn('http://example.com/');
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
        $expectedResult = 'http://example.com/?q=Some+test+search+query';
        
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
