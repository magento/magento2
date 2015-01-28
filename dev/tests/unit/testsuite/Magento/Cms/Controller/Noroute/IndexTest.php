<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Noroute;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cms\Controller\Noroute\Index
     */
    protected $_controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cmsHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $forwardFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Forward|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $forwardMock;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $responseMock = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $this->forwardFactoryMock = $this->getMockBuilder('Magento\Backend\Model\View\Result\ForwardFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->forwardMock = $this->getMockBuilder('Magento\Backend\Model\View\Result\Forward')
            ->disableOriginalConstructor()
            ->getMock();
        $this->forwardFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->forwardMock);
        $this->forwardMock->expects(
            $this->at(0)
        )->method(
            'setHeader'
        )->with(
            'HTTP/1.1',
            '404 Not Found'
        )->will(
            $this->returnSelf()
        );
        $this->forwardMock->expects(
            $this->at(1)
        )->method(
            'setHeader'
        )->with(
            'Status',
            '404 File not found'
        )->will(
            $this->returnSelf()
        );

        $scopeConfigMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->_cmsHelperMock = $this->getMock('Magento\Cms\Helper\Page', [], [], '', false);
        $valueMap = [
            [
                'Magento\Framework\App\Config\ScopeConfigInterface',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $scopeConfigMock,
            ],
            ['Magento\Cms\Helper\Page', $this->_cmsHelperMock],
        ];
        $objectManagerMock->expects($this->any())->method('get')->will($this->returnValueMap($valueMap));
        $scopeConfigMock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            \Magento\Cms\Helper\Page::XML_PATH_NO_ROUTE_PAGE
        )->will(
            $this->returnValue('pageId')
        );
        $this->_controller = $helper->getObject(
            'Magento\Cms\Controller\Noroute\Index',
            ['response' => $responseMock, 'objectManager' => $objectManagerMock, 'request' => $this->_requestMock,
            'resultForwardFactory' => $this->forwardFactoryMock
            ]
        );
    }

    /**
     * @param bool $renderPage
     * @dataProvider indexActionDataProvider
     */
    public function testIndexAction($renderPage)
    {
        $this->_cmsHelperMock->expects($this->once())->method('prepareResultPage')->will($this->returnValue($renderPage));
        $this->_requestMock->expects($this->any())->method('setActionName')->with('defaultNoRoute');
        $this->_controller->execute();
    }

    public function indexActionDataProvider()
    {
        return ['renderPage_return_true' => [true], 'renderPage_return_false' => [false]];
    }
}
