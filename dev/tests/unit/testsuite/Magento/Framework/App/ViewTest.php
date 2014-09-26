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
namespace Magento\Framework\App;

class ViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\View
     */
    protected $_view;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_layoutMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configScopeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_layoutProcessor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_actionFlagMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventManagerMock;

    /**
     * @var \Magento\Framework\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPage;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_layoutMock = $this->getMock('Magento\Framework\View\Layout', array(), array(), '', false);
        $this->_requestMock = $this->getMock('Magento\Framework\App\Request\Http', array(), array(), '', false);
        $this->_configScopeMock = $this->getMock('Magento\Framework\Config\ScopeInterface');
        $this->_layoutProcessor = $this->getMock('Magento\Core\Model\Layout\Merge', [], [], '', false);
        $this->_layoutMock->expects($this->any())->method('getUpdate')
            ->will($this->returnValue($this->_layoutProcessor));
        $this->_actionFlagMock = $this->getMock('Magento\Framework\App\ActionFlag', array(), array(), '', false);
        $this->_eventManagerMock = $this->getMock('Magento\Framework\Event\ManagerInterface');
        $this->resultPage = $this->getMockBuilder('Magento\Framework\View\Result\Page')
            ->setConstructorArgs(
                $helper->getConstructArguments('Magento\Framework\View\Result\Page', ['request' => $this->_requestMock])
            )
            ->setMethods(['getLayout', 'renderResult'])
            ->getMock();
        $this->resultPage->expects($this->any())
            ->method('getLayout')
            ->will($this->returnValue($this->_layoutMock));
        $pageFactory = $this->getMockBuilder('Magento\Framework\View\Result\PageFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $pageFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->resultPage));

        $this->response = $this->getMock('Magento\Framework\App\Response\Http', array(), array(), '', false);

        $this->_view = $helper->getObject(
            'Magento\Framework\App\View',
            array(
                'layout' => $this->_layoutMock,
                'request' => $this->_requestMock,
                'response' => $this->response,
                'configScope' => $this->_configScopeMock,
                'eventManager' => $this->_eventManagerMock,
                'actionFlag' => $this->_actionFlagMock,
                'pageFactory' => $pageFactory
            )
        );
    }

    public function testGetLayout()
    {
        $this->assertEquals($this->_layoutMock, $this->_view->getLayout());
    }

    /**
     * @expectedException \RuntimeException
     * @exceptedExceptionMessage 'Layout must be loaded only once.'
     */
    public function testLoadLayoutWhenLayoutAlreadyLoaded()
    {
        $this->_view->setIsLayoutLoaded(true);
        $this->_view->loadLayout();
    }

    public function testLoadLayoutWithDefaultSetup()
    {

        $this->_layoutProcessor->expects($this->at(0))->method('addHandle')->with('default');
        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getFullActionName'
        )->will(
            $this->returnValue('action_name')
        );
        $this->_layoutMock->expects(
            $this->once()
        )->method(
            'generateXml'
        )->will(
            $this->returnValue($this->_layoutMock)
        );
        $this->_layoutMock->expects(
            $this->once()
        )->method(
            'generateElements'
        )->will(
            $this->returnValue($this->_layoutMock)
        );
        $this->_view->loadLayout();
    }

    public function testLoadLayoutWhenBlocksNotGenerated()
    {
        $this->_layoutMock->expects($this->once())->method('generateXml');
        $this->_layoutMock->expects($this->never())->method('generateElements');
        $this->_view->loadLayout('', false, true);
    }

    public function testLoadLayoutWhenXmlNotGenerated()
    {
        $this->_layoutMock->expects($this->never())->method('generateElements');
        $this->_layoutMock->expects($this->never())->method('generateXml');
        $this->_view->loadLayout('', true, false);
    }

    public function testGetDefaultLayoutHandle()
    {
        $this->_requestMock->expects($this->once())
            ->method('getFullActionName')
            ->will($this->returnValue('ExpectedValue'));

        $this->assertEquals('expectedvalue', $this->_view->getDefaultLayoutHandle());
    }

    public function testAddActionLayoutHandlesWhenPageLayoutHandlesExist()
    {
        $this->_requestMock->expects($this->once())
            ->method('getFullActionName')
            ->will($this->returnValue('Full_Action_Name'));

        $this->_layoutProcessor->expects($this->once())
            ->method('addHandle')
            ->with('full_action_name');

        $this->_view->addActionLayoutHandles();
    }

    public function testAddPageLayoutHandles()
    {
        $pageHandles = array('full_action_name', 'full_action_name_key_value');
        $this->_requestMock->expects($this->once())
            ->method('getFullActionName')
            ->will($this->returnValue('Full_Action_Name'));

        $this->_layoutProcessor->expects($this->once())
            ->method('addHandle')
            ->with($pageHandles);
        $this->_view->addPageLayoutHandles(array('key' => 'value'));
    }

    public function testGenerateLayoutBlocksWhenFlagIsNotSet()
    {

        $valueMap = array(
            array('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH_BLOCK_EVENT, false),
            array('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH_BLOCK_EVENT, false)
        );
        $this->_actionFlagMock->expects($this->any())->method('get')->will($this->returnValueMap($valueMap));

        $eventArgument = array('full_action_name' => 'Full_Name', 'layout' => $this->_layoutMock);
        $this->_requestMock->expects(
            $this->exactly(2)
        )->method(
            'getFullActionName'
        )->will(
            $this->returnValue('Full_Name')
        );
        $this->_eventManagerMock->expects(
            $this->at(0)
        )->method(
            'dispatch'
        )->with(
            'controller_action_layout_generate_blocks_before',
            $eventArgument
        );
        $this->_eventManagerMock->expects(
            $this->at(1)
        )->method(
            'dispatch'
        )->with(
            'controller_action_layout_generate_blocks_after',
            $eventArgument
        );
        $this->_view->generateLayoutBlocks();
    }

    public function testGenerateLayoutBlocksWhenFlagIsSet()
    {

        $valueMap = array(
            array('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH_BLOCK_EVENT, true),
            array('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH_BLOCK_EVENT, true)
        );
        $this->_actionFlagMock->expects($this->any())->method('get')->will($this->returnValueMap($valueMap));

        $this->_eventManagerMock->expects($this->never())->method('dispatch');
        $this->_view->generateLayoutBlocks();
    }

    public function testRenderLayoutIfActionFlagExist()
    {
        $this->_actionFlagMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            '',
            'no-renderLayout'
        )->will(
            $this->returnValue(true)
        );
        $this->_eventManagerMock->expects($this->never())->method('dispatch');
        $this->_view->renderLayout();
    }

    public function testRenderLayoutWhenOutputNotEmpty()
    {
        $this->_actionFlagMock->expects($this->once())
            ->method('get')
            ->with('', 'no-renderLayout')
            ->will($this->returnValue(false));
        $this->_layoutMock->expects($this->once())->method('addOutputElement')->with('output');
        $this->resultPage->expects($this->once())->method('renderResult')->with($this->response);
        $this->_view->renderLayout('output');
    }

    public function testRenderLayoutWhenOutputEmpty()
    {
        $this->_actionFlagMock->expects($this->once())
            ->method('get')
            ->with('', 'no-renderLayout')
            ->will($this->returnValue(false));

        $this->_layoutMock->expects($this->never())->method('addOutputElement');
        $this->resultPage->expects($this->once())->method('renderResult')->with($this->response);
        $this->_view->renderLayout();
    }
}
