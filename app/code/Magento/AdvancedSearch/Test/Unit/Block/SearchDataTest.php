<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdvancedSearch\Test\Unit\Block;

use PHPUnit\Framework\MockObject\MockObject as MockObject;

class SearchDataTest extends \PHPUnit\Framework\TestCase
{
    /** @var  \Magento\Framework\View\Element\Template\Context|MockObject */
    private $context;

    /**
     * @var \Magento\Search\Model\QueryFactoryInterface|MockObject
     */
    private $queryFactory;

    /**
     * @var \Magento\Search\Model\Query|MockObject
     */
    private $searchQuery;

    /**
     * @var \Magento\AdvancedSearch\Model\SuggestedQueriesInterface|MockObject
     */
    private $dataProvider;

    /**
     * @var \Magento\AdvancedSearch\Block\SearchData
     */
    private $block;

    protected function setUp(): void
    {
        $this->dataProvider = $this->getMockBuilder(\Magento\AdvancedSearch\Model\SuggestedQueriesInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItems', 'isResultsCountEnabled'])
            ->getMockForAbstractClass();

        $this->searchQuery = $this->getMockBuilder(\Magento\Search\Model\QueryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQueryText'])
            ->getMockForAbstractClass();
        $this->queryFactory = $this->getMockBuilder(\Magento\Search\Model\QueryFactoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $this->queryFactory->expects($this->once())
            ->method('get')
            ->willReturn($this->searchQuery);
        $this->context = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->block = $this->getMockBuilder(\Magento\AdvancedSearch\Block\SearchData::class)->setConstructorArgs(
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
            ->willReturn($value);
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
            ->willReturn($value);
        $this->assertEquals($value, $this->block->isShowResultsCount());
    }
}
