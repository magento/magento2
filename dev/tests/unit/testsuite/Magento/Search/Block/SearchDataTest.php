<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Block;

use PHPUnit_Framework_MockObject_MockObject as MockObject;

class SearchDataTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Search\Model\SearchDataProviderInterface|MockObject
     */
    private $dataProvider;

    /**
     * @var \Magento\Search\Block\SearchData
     */
    private $block;

    protected function setUp()
    {
        $this->dataProvider = $this->getMockBuilder('\Magento\Search\Model\SearchDataProviderInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getSearchData', 'isCountResultsEnabled'])
            ->getMockForAbstractClass();

        $this->searchQuery = $this->getMockBuilder('\Magento\Search\Model\QueryInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getQueryText'])
            ->getMockForAbstractClass();
        $this->queryFactory = $this->getMockBuilder('\Magento\Search\Model\QueryFactoryInterface')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $this->queryFactory->expects($this->once())
            ->method('get')
            ->will($this->returnValue($this->searchQuery));
        $this->context = $this->getMockBuilder('\Magento\Framework\View\Element\Template\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->block = $this->getMockBuilder('\Magento\Search\Block\SearchData')->setConstructorArgs(
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
            ->method('getSearchData')
            ->with($this->searchQuery)
            ->will($this->returnValue($value));
        $actualValue = $this->block->getSearchData();
        $this->assertEquals($value, $actualValue);
    }

    public function testGetLink()
    {
        $searchQuery = 'Some test search query';
        $expectedResult = '?q=Some+test+search+query';
        $actualResult = $this->block->getLink($searchQuery);
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testIsCountResultsEnabled()
    {
        $value = 'qwertyasdfzxcv';
        $this->dataProvider->expects($this->once())
            ->method('isCountResultsEnabled')
            ->will($this->returnValue($value));
        $this->assertEquals($value, $this->block->isCountResultsEnabled());
    }
}
