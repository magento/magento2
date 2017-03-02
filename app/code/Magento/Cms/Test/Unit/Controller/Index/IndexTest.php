<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller\Index;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cms\Controller\Index\Index
     */
    protected $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cmsHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

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

    /**
     * @var string
     */
    protected $pageId = 'home';

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $responseMock = $this->getMock(\Magento\Framework\App\Response\Http::class, [], [], '', false);
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

        $scopeConfigMock = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->requestMock = $this->getMock(\Magento\Framework\App\Request\Http::class, [], [], '', false);
        $this->cmsHelperMock = $this->getMock(\Magento\Cms\Helper\Page::class, [], [], '', false);
        $valueMap = [
            [\Magento\Framework\App\Config\ScopeConfigInterface::class,
                $scopeConfigMock,
            ],
            [\Magento\Cms\Helper\Page::class, $this->cmsHelperMock],
        ];
        $objectManagerMock->expects($this->any())->method('get')->willReturnMap($valueMap);
        $scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                \Magento\Cms\Helper\Page::XML_PATH_HOME_PAGE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ->willReturn($this->pageId);
        $this->controller = $helper->getObject(
            \Magento\Cms\Controller\Index\Index::class,
            [
                'response' => $responseMock,
                'objectManager' => $objectManagerMock,
                'request' => $this->requestMock,
                'resultForwardFactory' => $this->forwardFactoryMock
            ]
        );
    }

    public function testExecuteResultPage()
    {
        $this->cmsHelperMock->expects($this->once())
            ->method('prepareResultPage')
            ->with($this->controller, $this->pageId)
            ->willReturn($this->resultPageMock);
        $this->assertSame($this->resultPageMock, $this->controller->execute());
    }

    public function testExecuteResultForward()
    {
        $this->forwardMock->expects($this->once())
            ->method('forward')
            ->with('defaultIndex')
            ->willReturnSelf();
        $this->assertSame($this->forwardMock, $this->controller->execute());
    }
}
