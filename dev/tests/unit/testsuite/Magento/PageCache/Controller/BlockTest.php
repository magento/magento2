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
 * @category    Magento
 * @package     Magento_PageCache
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\PageCache\Controller\Block
 */
namespace Magento\PageCache\Controller;

/**
 * Class BlockTest
 *
 * @package Magento\PageCache\Controller
 */
class BlockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\App\View|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewMock;

    /**
     * @var \Magento\PageCache\Controller\Block
     */
    protected $controller;

    /**
     * @var \Magento\Core\Model\Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * Set up before test
     */
    protected function setUp()
    {
        $this->layoutMock = $this->getMockBuilder(
            'Magento\Core\Model\Layout'
        )->disableOriginalConstructor()->getMock();

        $contextMock = $this->getMockBuilder('Magento\App\Action\Context')->disableOriginalConstructor()->getMock();

        $this->requestMock = $this->getMockBuilder(
            'Magento\App\Request\Http'
        )->disableOriginalConstructor()->getMock();
        $this->responseMock = $this->getMockBuilder(
            'Magento\App\Response\Http'
        )->disableOriginalConstructor()->getMock();
        $this->viewMock = $this->getMockBuilder('Magento\App\View')->disableOriginalConstructor()->getMock();

        $contextMock->expects($this->any())->method('getRequest')->will($this->returnValue($this->requestMock));
        $contextMock->expects($this->any())->method('getResponse')->will($this->returnValue($this->responseMock));
        $contextMock->expects($this->any())->method('getView')->will($this->returnValue($this->viewMock));

        $this->controller = new \Magento\PageCache\Controller\Block($contextMock);
    }

    public function testRenderActionNotAjax()
    {
        $this->requestMock->expects($this->once())->method('isAjax')->will($this->returnValue(false));
        $this->requestMock->expects($this->once())->method('setActionName')->will($this->returnValue('noroute'));
        $this->requestMock->expects($this->once())->method('setDispatched')->will($this->returnValue(false));
        $this->controller->renderAction();
    }

    /**
     * Test no params: blocks, handles
     */
    public function testRenderActionNoParams()
    {
        $this->requestMock->expects($this->once())->method('isAjax')->will($this->returnValue(true));
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with($this->equalTo('blocks'), $this->equalTo(''))
            ->will($this->returnValue(''));
        $this->requestMock->expects($this->at(2))
            ->method('getParam')
            ->with($this->equalTo('handles'), $this->equalTo(''))
            ->will($this->returnValue(''));
        $this->controller->renderAction();
    }

    public function testRenderAction()
    {
        $blocks = array('block1', 'block2');
        $handles = array('handle1', 'handle2');
        $expectedData = array('block1' => 'data1', 'block2' => 'data2');

        $blockInstance1 = $this->getMock(
            'Magento\PageCache\Block\Controller\StubBlock',
            array('toHtml'),
            array(),
            '',
            false
        );
        $blockInstance1->expects($this->once())->method('toHtml')->will($this->returnValue($expectedData['block1']));

        $blockInstance2 = $this->getMock(
            'Magento\PageCache\Block\Controller\StubBlock',
            array('toHtml'),
            array(),
            '',
            false
        );
        $blockInstance2->expects($this->once())->method('toHtml')->will($this->returnValue($expectedData['block2']));

        $this->requestMock->expects($this->once())->method('isAjax')->will($this->returnValue(true));
        $this->requestMock->expects(
            $this->at(1)
        )->method(
            'getParam'
        )->with(
            $this->equalTo('blocks'),
            $this->equalTo('')
        )->will(
            $this->returnValue(json_encode($blocks))
        );
        $this->requestMock->expects(
            $this->at(2)
        )->method(
            'getParam'
        )->with(
            $this->equalTo('handles'),
            $this->equalTo('')
        )->will(
            $this->returnValue(json_encode($handles))
        );
        $this->viewMock->expects($this->once())->method('loadLayout')->with($this->equalTo($handles));
        $this->viewMock->expects($this->any())->method('getLayout')->will($this->returnValue($this->layoutMock));
        $this->layoutMock->expects(
            $this->at(0)
        )->method(
            'getBlock'
        )->with(
            $this->equalTo($blocks[0])
        )->will(
            $this->returnValue($blockInstance1)
        );
        $this->layoutMock->expects(
            $this->at(1)
        )->method(
            'getBlock'
        )->with(
            $this->equalTo($blocks[1])
        )->will(
            $this->returnValue($blockInstance2)
        );

        $this->responseMock->expects(
            $this->once()
        )->method(
            'appendBody'
        )->with(
            $this->equalTo(json_encode($expectedData))
        );

        $this->controller->renderAction();
    }

    /**
     * @dataProvider esiActionDataProvider
     * @param string $blockClass
     * @param bool $shouldSetHeaders
     */
    public function testEsiAction($blockClass, $shouldSetHeaders)
    {
        $block = 'block';
        $handles = array('handle1', 'handle2');
        $html = 'some-html';
        $mapData = array(array('blocks', '', json_encode(array($block))), array('handles', '', json_encode($handles)));

        $blockInstance1 = $this->getMock(
            $blockClass,
            array('toHtml'),
            array(),
            '',
            false
        );

        $blockInstance1->expects($this->once())->method('toHtml')->will($this->returnValue($html));
        $blockInstance1->setTtl(360);

        $this->requestMock->expects($this->any())->method('getParam')->will($this->returnValueMap($mapData));

        $this->viewMock->expects($this->once())->method('loadLayout')->with($this->equalTo($handles));

        $this->viewMock->expects($this->once())->method('getLayout')->will($this->returnValue($this->layoutMock));

        $this->layoutMock->expects(
            $this->once()
        )->method(
            'getBlock'
        )->with(
            $this->equalTo($block)
        )->will(
            $this->returnValue($blockInstance1)
        );

        if ($shouldSetHeaders) {
            $this->responseMock->expects($this->once())
                ->method('setHeader')
                ->with('X-Magento-Tags', implode(',', $blockInstance1->getIdentities()));
        } else {
            $this->responseMock->expects($this->never())
                ->method('setHeader');
        }

        $this->responseMock->expects($this->once())
            ->method('appendBody')
            ->with($this->equalTo($html));

        $this->controller->esiAction();
    }

    public function esiActionDataProvider()
    {
        return array(
            array('Magento\PageCache\Block\Controller\StubBlock', true),
            array('Magento\View\Element\AbstractBlock', false),
        );
    }

    public function testEsiActionBlockNotExists()
    {
        $handles = json_encode(array('handle1', 'handle2'));
        $mapData = array(
            array('blocks', '', null),
            array('handles', '', $handles)
        );

        $this->requestMock->expects($this->any())->method('getParam')->will($this->returnValueMap($mapData));
        $this->viewMock->expects($this->never())->method('getLayout')->will($this->returnValue($this->layoutMock));

        $this->controller->esiAction();
    }
}
