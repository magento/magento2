<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Controller\Noroute;

use Magento\Cms\Controller\Noroute\Index;
use Magento\Cms\Helper\Page as CmsPage;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Result\Page;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexTest extends TestCase
{
    /**
     * @var Index
     */
    protected Index $_controller;

    /**
     * @var MockObject
     */
    protected MockObject $_cmsHelperMock;

    /**
     * @var MockObject
     */
    protected MockObject $_requestMock;

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

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $responseMock = $this->createMock(Http::class);
        $this->resultPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->forwardFactoryMock = $this->getMockBuilder(ForwardFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->forwardMock = $this->getMockBuilder(Forward::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->forwardFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->forwardMock);

        $scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->_requestMock = $this->createMock(RequestHttp::class);
        $this->_cmsHelperMock = $this->createMock(CmsPage::class);
        $valueMap = [
            [
                ScopeConfigInterface::class,
                ScopeInterface::SCOPE_STORE,
                $scopeConfigMock,
            ],
            [CmsPage::class, $this->_cmsHelperMock]
        ];
        $objectManagerMock->expects($this->any())->method('get')->willReturnMap($valueMap);
        $scopeConfigMock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            CmsPage::XML_PATH_NO_ROUTE_PAGE
        )->willReturn(
            'pageId'
        );
        $this->_controller = $helper->getObject(
            Index::class,
            [
                'response' => $responseMock,
                'objectManager' => $objectManagerMock,
                'request' => $this->_requestMock,
                'resultForwardFactory' => $this->forwardFactoryMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecuteResultPage(): void
    {
        $this->resultPageMock
            ->method('setStatusHeader')
            ->with(404, '1.1', 'Not Found')
            ->willReturn($this->resultPageMock);

        $this->resultPageMock
            ->method('setHeader')
            ->withConsecutive(
                ['Status', '404 File not found'],
                ['Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0']
            )->willReturn($this->resultPageMock);

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

    /**
     * @return void
     */
    public function testExecuteResultForward(): void
    {
        $this->forwardMock->expects(
            $this->once()
        )->method(
            'setController'
        )->with(
            'index'
        )->willReturnSelf();
        $this->forwardMock->expects(
            $this->once()
        )->method(
            'forward'
        )->with(
            'defaultNoRoute'
        )->willReturnSelf();
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
