<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\App\Action;

/**
 * Class ContextPluginTest
 */
class ContextPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContextPlugin
     */
    protected $plugin;

    /**
     * @var \Magento\Catalog\Model\Product\ProductList\Toolbar|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $toolbarModelMock;

    /**
     * @var \Magento\Framework\App\Http\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpContextMock;

    /**
     * @var \Magento\Catalog\Helper\Product\ProductList|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productListHelperMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * Set up
     */
    public function setUp()
    {
        $this->toolbarModelMock = $this->getMock(
            'Magento\Catalog\Model\Product\ProductList\Toolbar',
            [
                'getDirection',
                'getOrder',
                'getMode',
                'getLimit'
            ],
            [],
            '',
            false
        );
        $this->closureMock = function () {
            return 'ExpectedValue';
        };
        $this->subjectMock = $this->getMock('Magento\Framework\App\Action\Action', [], [], '', false);
        $this->requestMock = $this->getMock('Magento\Framework\App\RequestInterface');
        $this->httpContextMock = $this->getMock('Magento\Framework\App\Http\Context', [], [], '', false);
        $this->productListHelperMock = $this->getMock('Magento\Catalog\Helper\Product\ProductList',
            [], [], '', false);
        $this->plugin = new ContextPlugin(
            $this->toolbarModelMock,
            $this->httpContextMock,
            $this->productListHelperMock
        );
    }

    public function testAroundDispatchHasSortDirection()
    {
        $this->toolbarModelMock->expects($this->exactly(1))
            ->method('getDirection')
            ->will($this->returnValue('asc'));
        $this->toolbarModelMock->expects($this->once())
            ->method('getOrder')
            ->will($this->returnValue('Name'));
        $this->toolbarModelMock->expects($this->once())
            ->method('getMode')
            ->will($this->returnValue('list'));
        $this->toolbarModelMock->expects($this->once())
            ->method('getLimit')
            ->will($this->returnValue([1 => 1, 2 => 2]));
        $this->productListHelperMock->expects($this->once())
            ->method('getDefaultSortField')
            ->will($this->returnValue('Field'));
        $this->productListHelperMock->expects($this->exactly(2))
            ->method('getDefaultViewMode')
            ->will($this->returnValue('grid'));
        $this->productListHelperMock->expects($this->once())
            ->method('getDefaultLimitPerPageValue')
            ->will($this->returnValue([10 => 10]));
        $this->httpContextMock->expects($this->exactly(4))
            ->method('setValue')
            ->will($this->returnValueMap([
                [
                    \Magento\Catalog\Helper\Data::CONTEXT_CATALOG_SORT_DIRECTION,
                    'asc',
                    \Magento\Catalog\Helper\Product\ProductList::DEFAULT_SORT_DIRECTION,
                    $this->httpContextMock,
                ], [
                    \Magento\Catalog\Helper\Data::CONTEXT_CATALOG_SORT_ORDER,
                    'Name',
                    'Field',
                    $this->httpContextMock
                ], [
                    \Magento\Catalog\Helper\Data::CONTEXT_CATALOG_DISPLAY_MODE,
                    'list',
                    'grid',
                    $this->httpContextMock
                ], [
                    \Magento\Catalog\Helper\Data::CONTEXT_CATALOG_LIMIT,
                    [1 => 1, 2 => 2], [10 => 10]
                ],
            ]));
        $this->assertEquals(
            'ExpectedValue',
            $this->plugin->aroundDispatch($this->subjectMock, $this->closureMock, $this->requestMock)
        );
    }
}
