<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller\Noroute;

use Magento\Cms\Controller\Noroute\Index;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Result\Page;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    /**
     * @var Index
     */
    protected $_controller;

    /**
     * @var MockObject
     */
    protected $_cmsHelperMock;

    /**
     * @var MockObject
     */
    protected $_requestMock;

    /**
     * @var ForwardFactory|MockObject
     */
    protected $forwardFactoryMock;

    /**
     * @var Forward|MockObject
     */
    protected $forwardMock;

    /**
     * @var Page|MockObject
     */
    protected $resultPageMock;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $responseMock = $this->createMock(Http::class);
        $this->resultPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->forwardFactoryMock = $this->getMockBuilder(ForwardFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->forwardMock = $this->getMockBuilder(Forward::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->forwardFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->forwardMock);

        $scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->_requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->_cmsHelperMock = $this->createMock(\Magento\Cms\Helper\Page::class);
        $valueMap = [
            [ScopeConfigInterface::class,
                ScopeInterface::SCOPE_STORE,
                $scopeConfigMock,
            ],
            [\Magento\Cms\Helper\Page::class, $this->_cmsHelperMock],
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
            Index::class,
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
