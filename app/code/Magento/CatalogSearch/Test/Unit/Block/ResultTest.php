<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Block;

use \Magento\CatalogSearch\Block\Result;

/**
 * Unit test for \Magento\CatalogSearch\Block\Result
 */
class ResultTest extends \PHPUnit\Framework\TestCase
{
    /** @var  \Magento\Search\Model\Query|\PHPUnit\Framework\MockObject\MockObject */
    private $queryMock;

    /** @var  \Magento\Search\Model\QueryFactory|\PHPUnit\Framework\MockObject\MockObject */
    private $queryFactoryMock;

    /** @var \Magento\CatalogSearch\Block\Result */
    protected $model;

    /** @var \Magento\Framework\View\Element\Template\Context|\PHPUnit\Framework\MockObject\MockObject */
    protected $contextMock;

    /** @var \Magento\Catalog\Model\Layer|\PHPUnit\Framework\MockObject\MockObject */
    protected $layerMock;

    /** @var \Magento\CatalogSearch\Helper\Data|\PHPUnit\Framework\MockObject\MockObject */
    protected $dataMock;

    /**
     * @var \Magento\Catalog\Block\Product\ListProduct|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $childBlockMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $this->layerMock = $this->createMock(\Magento\Catalog\Model\Layer\Search::class);
        /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Model\Layer\Resolver $layerResolver */
        $layerResolver = $this->getMockBuilder(\Magento\Catalog\Model\Layer\Resolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'create'])
            ->getMock();
        $layerResolver->expects($this->any())
            ->method($this->anything())
            ->willReturn($this->layerMock);
        $this->dataMock = $this->createMock(\Magento\CatalogSearch\Helper\Data::class);
        $this->queryMock = $this->getMockBuilder(\Magento\Search\Model\Query::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryFactoryMock = $this->getMockBuilder(\Magento\Search\Model\QueryFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $this->model = new Result($this->contextMock, $layerResolver, $this->dataMock, $this->queryFactoryMock);
    }

    public function testGetSearchQueryText()
    {
        $this->dataMock->expects($this->once())->method('getEscapedQueryText')->willReturn('query_text');
        $this->assertEquals('Search results for: \'query_text\'', $this->model->getSearchQueryText());
    }

    public function testGetNoteMessages()
    {
        $this->dataMock->expects($this->once())->method('getNoteMessages')->willReturn('SOME-MESSAGE');
        $this->assertEquals('SOME-MESSAGE', $this->model->getNoteMessages());
    }

    /**
     * @param bool $isMinQueryLength
     * @param string $expectedResult
     * @dataProvider getNoResultTextDataProvider
     */
    public function testGetNoResultText($isMinQueryLength, $expectedResult)
    {
        $this->dataMock->expects(
            $this->once()
        )->method(
            'isMinQueryLength'
        )->willReturn(
            $isMinQueryLength
        );
        if ($isMinQueryLength) {
            $queryMock = $this->createMock(\Magento\Search\Model\Query::class);
            $queryMock->expects($this->once())->method('getMinQueryLength')->willReturn('5');

            $this->queryFactoryMock->expects($this->once())->method('get')->willReturn($queryMock);
        }
        $this->assertEquals($expectedResult, $this->model->getNoResultText());
    }

    /**
     * @return array
     */
    public function getNoResultTextDataProvider()
    {
        return [[true, 'Minimum Search query length is 5'], [false, null]];
    }
}
