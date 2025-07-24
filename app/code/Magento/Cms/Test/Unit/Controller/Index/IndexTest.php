<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Controller\Index;

use Magento\Cms\Controller\Index\Index;
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

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexTest extends TestCase
{
    /**
     * @var Index
     */
    protected $controller;

    /**
     * @var MockObject
     */
    protected $cmsHelperMock;

    /**
     * @var MockObject
     */
    protected $requestMock;

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
     * @var string
     */
    protected $pageId = 'home';

    /**
     * Test setUp
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
        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->cmsHelperMock = $this->createMock(\Magento\Cms\Helper\Page::class);
        $valueMap = [
            [ScopeConfigInterface::class,
                $scopeConfigMock,
            ],
            [\Magento\Cms\Helper\Page::class, $this->cmsHelperMock],
        ];
        $objectManagerMock->expects($this->any())->method('get')->willReturnMap($valueMap);
        $scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                \Magento\Cms\Helper\Page::XML_PATH_HOME_PAGE,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn($this->pageId);
        $this->controller = $helper->getObject(
            Index::class,
            [
                'response' => $responseMock,
                'objectManager' => $objectManagerMock,
                'request' => $this->requestMock,
                'resultForwardFactory' => $this->forwardFactoryMock,
                'scopeConfig' => $scopeConfigMock,
                'page' => $this->cmsHelperMock
            ]
        );
    }

    /**
     * Controller test
     */
    public function testExecuteResultPage()
    {
        $this->cmsHelperMock->expects($this->once())
            ->method('prepareResultPage')
            ->with($this->controller, $this->pageId)
            ->willReturn($this->resultPageMock);
        $this->assertSame($this->resultPageMock, $this->controller->execute());
    }

    /**
     * Controller test
     */
    public function testExecuteResultForward()
    {
        $this->forwardMock->expects($this->once())
            ->method('forward')
            ->with('defaultIndex')
            ->willReturnSelf();
        $this->assertSame($this->forwardMock, $this->controller->execute());
    }
}
