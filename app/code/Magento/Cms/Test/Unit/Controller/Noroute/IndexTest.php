<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller\Noroute;

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
     * @var \Magento\Framework\Controller\Result\ForwardFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $forwardFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\Forward|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $forwardMock;

    /**
     * @var \Magento\Framework\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageMock;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $responseMock = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $this->resultPageMock = $this->getMockBuilder('\Magento\Framework\View\Result\Page')
            ->disableOriginalConstructor()
            ->getMock();
        $this->forwardFactoryMock = $this->getMockBuilder('\Magento\Framework\Controller\Result\ForwardFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->forwardMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Forward')
            ->disableOriginalConstructor()
            ->getMock();
        $this->forwardFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->forwardMock);

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

    public function testExecuteResultPage()
    {
        $this->resultPageMock->expects(
            $this->at(0)
        )->method(
            'setStatusHeader'
        )->with(404, '1.1', 'Not Found')->will(
            $this->returnSelf()
        );
        $this->resultPageMock->expects(
            $this->at(1)
        )->method(
            'setHeader'
        )->with(
            'Status',
            '404 File not found'
        )->will(
            $this->returnSelf()
        );
        $this->_cmsHelperMock->expects(
            $this->once()
        )->method(
            'prepareResultPage'
        )->will(
            $this->returnValue($this->resultPageMock)
        );
        $this->assertSame(
            $this->resultPageMock,
            $this->_controller->execute()
        );
    }

    public function testExecuteResultForward()
    {
        $this->forwardMock->expects(
            $this->once()
        )->method(
            'setController'
        )->with(
            'index'
        )->will(
            $this->returnSelf()
        );
        $this->forwardMock->expects(
            $this->once()
        )->method(
            'forward'
        )->with(
            'defaultNoRoute'
        )->will(
            $this->returnSelf()
        );
        $this->_cmsHelperMock->expects(
            $this->once()
        )->method(
            'prepareResultPage'
        )->will(
            $this->returnValue(false)
        );
        $this->assertSame(
            $this->forwardMock,
            $this->_controller->execute()
        );
    }
}
