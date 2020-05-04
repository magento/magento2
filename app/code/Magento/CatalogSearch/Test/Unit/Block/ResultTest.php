<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Block;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Layer\Search;
use Magento\CatalogSearch\Block\Result;
use Magento\CatalogSearch\Helper\Data;
use Magento\Framework\View\Element\Template\Context;
use Magento\Search\Model\Query;
use Magento\Search\Model\QueryFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\CatalogSearch\Block\Result
 */
class ResultTest extends TestCase
{
    /** @var  Query|MockObject */
    private $queryMock;

    /** @var  QueryFactory|MockObject */
    private $queryFactoryMock;

    /** @var Result */
    protected $model;

    /** @var Context|MockObject */
    protected $contextMock;

    /** @var Layer|MockObject */
    protected $layerMock;

    /** @var Data|MockObject */
    protected $dataMock;

    /**
     * @var ListProduct|MockObject
     */
    protected $childBlockMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->layerMock = $this->createMock(Search::class);
        /** @var MockObject|Resolver $layerResolver */
        $layerResolver = $this->getMockBuilder(Resolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'create'])
            ->getMock();
        $layerResolver->expects($this->any())
            ->method($this->anything())
            ->willReturn($this->layerMock);
        $this->dataMock = $this->createMock(Data::class);
        $this->queryMock = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryFactoryMock = $this->getMockBuilder(QueryFactory::class)
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
            $queryMock = $this->createMock(Query::class);
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
