<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Block;

use \Magento\CatalogSearch\Block\Result;

/**
 * Unit test for \Magento\CatalogSearch\Block\Result
 */
class ResultTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Search\Model\Query|\PHPUnit_Framework_MockObject_MockObject */
    private $queryMock;

    /** @var  \Magento\Search\Model\QueryFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $queryFactoryMock;

    /** @var \Magento\CatalogSearch\Block\Result */
    protected $model;

    /** @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    /** @var \Magento\Catalog\Model\Layer|\PHPUnit_Framework_MockObject_MockObject */
    protected $layerMock;

    /** @var \Magento\CatalogSearch\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $dataMock;

    /**
     * @var \Magento\Catalog\Block\Product\ListProduct|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $childBlockMock;

    protected function setUp()
    {
        $this->contextMock = $this->getMock('Magento\Framework\View\Element\Template\Context', [], [], '', false);
        $this->layerMock = $this->getMock('Magento\Catalog\Model\Layer\Search', [], [], '', false);
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Layer\Resolver $layerResolver */
        $layerResolver = $this->getMockBuilder('\Magento\Catalog\Model\Layer\Resolver')
            ->disableOriginalConstructor()
            ->setMethods(['get', 'create'])
            ->getMock();
        $layerResolver->expects($this->any())
            ->method($this->anything())
            ->will($this->returnValue($this->layerMock));
        $this->dataMock = $this->getMock('Magento\CatalogSearch\Helper\Data', [], [], '', false);
        $this->queryMock = $this->getMockBuilder('Magento\Search\Model\Query')
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryFactoryMock = $this->getMockBuilder('Magento\Search\Model\QueryFactory')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $this->model = new Result($this->contextMock, $layerResolver, $this->dataMock, $this->queryFactoryMock);
    }

    public function testGetSearchQueryText()
    {
        $this->dataMock->expects($this->once())->method('getEscapedQueryText')->will($this->returnValue('query_text'));
        $this->assertEquals('Search results for: \'query_text\'', $this->model->getSearchQueryText());
    }

    public function testGetNoteMessages()
    {
        $this->dataMock->expects($this->once())->method('getNoteMessages')->will($this->returnValue('SOME-MESSAGE'));
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
        )->will(
            $this->returnValue($isMinQueryLength)
        );
        if ($isMinQueryLength) {
            $queryMock = $this->getMock('Magento\Search\Model\Query', [], [], '', false);
            $queryMock->expects($this->once())->method('getMinQueryLength')->will($this->returnValue('5'));

            $this->queryFactoryMock->expects($this->once())->method('get')->will($this->returnValue($queryMock));
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
