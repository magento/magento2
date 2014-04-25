<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CatalogSearch\Block;

/**
 * Unit test for \Magento\CatalogSearch\Block\Result
 */
class ResultTest extends \PHPUnit_Framework_TestCase
{
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

    public function setUp()
    {
        $this->contextMock = $this->getMock('Magento\Framework\View\Element\Template\Context', [], [], '', false);
        $this->layerMock = $this->getMock('Magento\Catalog\Model\Layer\Search', [], [], '', false);
        $this->dataMock = $this->getMock('Magento\CatalogSearch\Helper\Data', [], [], '', false);
        $this->model = new Result($this->contextMock, $this->layerMock, $this->dataMock);
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
            $queryMock = $this->getMock('Magento\CatalogSearch\Model\Query', array(), array(), '', false);
            $queryMock->expects($this->once())->method('getMinQueryLength')->will($this->returnValue('5'));

            $this->dataMock->expects($this->once())->method('getQuery')->will($this->returnValue($queryMock));
        }
        $this->assertEquals($expectedResult, $this->model->getNoResultText());
    }

    /**
     * @return array
     */
    public function getNoResultTextDataProvider()
    {
        return array(array(true, 'Minimum Search query length is 5'), array(false, null));
    }
}
