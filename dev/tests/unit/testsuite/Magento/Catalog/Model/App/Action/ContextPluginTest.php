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
            array(
                'getDirection',
                'getOrder',
                'getMode',
                'getLimit'
            ),
            array(),
            '',
            false
        );
        $this->closureMock = function () {
            return 'ExpectedValue';
        };
        $this->subjectMock = $this->getMock('Magento\Framework\App\Action\Action', array(), array(), '', false);
        $this->requestMock = $this->getMock('Magento\Framework\App\RequestInterface');
        $this->httpContextMock = $this->getMock('Magento\Framework\App\Http\Context', array(), array(), '', false);
        $this->productListHelperMock = $this->getMock('Magento\Catalog\Helper\Product\ProductList',
            array(), array(), '', false);
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
            ->will($this->returnValue(array(1 => 1, 2 => 2)));
        $this->productListHelperMock->expects($this->once())
            ->method('getDefaultSortField')
            ->will($this->returnValue('Field'));
        $this->productListHelperMock->expects($this->exactly(2))
            ->method('getDefaultViewMode')
            ->will($this->returnValue('grid'));
        $this->productListHelperMock->expects($this->once())
            ->method('getDefaultLimitPerPageValue')
            ->will($this->returnValue(array(10=>10)));
        $this->httpContextMock->expects($this->exactly(4))
            ->method('setValue')
            ->will($this->returnValueMap(array(
                array(
                    \Magento\Catalog\Helper\Data::CONTEXT_CATALOG_SORT_DIRECTION,
                    'asc',
                    \Magento\Catalog\Helper\Product\ProductList::DEFAULT_SORT_DIRECTION,
                    $this->httpContextMock
                ), array(
                    \Magento\Catalog\Helper\Data::CONTEXT_CATALOG_SORT_ORDER,
                    'Name',
                    'Field',
                    $this->httpContextMock
                ), array(
                    \Magento\Catalog\Helper\Data::CONTEXT_CATALOG_DISPLAY_MODE,
                    'list',
                    'grid',
                    $this->httpContextMock
                ), array(
                    \Magento\Catalog\Helper\Data::CONTEXT_CATALOG_LIMIT,
                    array(1 => 1, 2 => 2), array (10 => 10)
                )
            )));
        $this->assertEquals(
            'ExpectedValue',
            $this->plugin->aroundDispatch($this->subjectMock, $this->closureMock, $this->requestMock)
        );
    }
}
