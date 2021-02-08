<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller\Noroute;

class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Cms\Controller\Noroute\Index
     */
    protected $_controller;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_cmsHelperMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_requestMock;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $forwardFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\Forward|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $forwardMock;

    /**
     * @var \Magento\Framework\View\Result\Page|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultPageMock;

    protected function setUp(): void
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $responseMock = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $this->resultPageMock = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->forwardFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\ForwardFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->forwardMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Forward::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->forwardFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->forwardMock);

        $scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->_requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->_cmsHelperMock = $this->createMock(\Magento\Cms\Helper\Page::class);
        $valueMap = [
            [\Magento\Framework\App\Config\ScopeConfigInterface::class,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $scopeConfigMock,
            ],
            [\Magento\Cms\Helper\Page::class, $this->_cmsHelperMock],
        ];
        $objectManagerMock->expects($this->any())->method('get')->willReturnMap($valueMap);
        $scopeConfigMock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            \Magento\Cms\Helper\Page::XML_PATH_NO_ROUTE_PAGE
        )->willReturn(
            'pageId'
        );
        $this->_controller = $helper->getObject(
            \Magento\Cms\Controller\Noroute\Index::class,
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
        )->with(404, '1.1', 'Not Found')->willReturnSelf(
            
        );
        $this->resultPageMock->expects(
            $this->at(1)
        )->method(
            'setHeader'
        )->with(
            'Status',
            '404 File not found'
        )->willReturnSelf(
            
        );
        $this->_cmsHelperMock->expects(
            $this->once()
        )->method(
            'prepareResultPage'
        )->willReturn(
            $this->resultPageMock
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
        )->willReturnSelf(
            
        );
        $this->forwardMock->expects(
            $this->once()
        )->method(
            'forward'
        )->with(
            'defaultNoRoute'
        )->willReturnSelf(
            
        );
        $this->_cmsHelperMock->expects(
            $this->once()
        )->method(
            'prepareResultPage'
        )->willReturn(
            false
        );
        $this->assertSame(
            $this->forwardMock,
            $this->_controller->execute()
        );
    }
}
