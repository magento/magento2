<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Page;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Cms\Controller\Adminhtml\Page\PostDataProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataProcessorMock;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataPersistorMock;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirect;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManagerMock;

    /**
     * @var \Magento\Cms\Model\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pageFactory;

    /**
     * @var \Magento\Cms\Api\PageRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pageRepository;

    /**
     * @var \Magento\Cms\Controller\Adminhtml\Page\Save
     */
    private $saveController;

    /**
     * @var int
     */
    private $pageId = 1;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->resultRedirectFactory = $this->getMockBuilder(\Magento\Backend\Model\View\Result\RedirectFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultRedirect = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultRedirect);
        $this->dataProcessorMock = $this->getMockBuilder(
            \Magento\Cms\Controller\Adminhtml\Page\PostDataProcessor::class
        )->disableOriginalConstructor()
            ->setMethods(['filter'])
            ->getMock();
        $this->dataPersistorMock = $this->getMockBuilder(\Magento\Framework\App\Request\DataPersistorInterface::class)
            ->getMock();
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['getParam', 'getPostValue'])
            ->getMockForAbstractClass();
        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->getMockForAbstractClass();
        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->setMethods(['dispatch'])
            ->getMockForAbstractClass();
        $this->pageFactory = $this->getMockBuilder(\Magento\Cms\Model\PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->pageRepository = $this->getMockBuilder(\Magento\Cms\Api\PageRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->saveController = $objectManager->getObject(
            \Magento\Cms\Controller\Adminhtml\Page\Save::class,
            [
                'request' => $this->requestMock,
                'messageManager' => $this->messageManagerMock,
                'eventManager' => $this->eventManagerMock,
                'resultRedirectFactory' => $this->resultRedirectFactory,
                'dataProcessor' => $this->dataProcessorMock,
                'dataPersistor' => $this->dataPersistorMock,
                'pageFactory' => $this->pageFactory,
                'pageRepository' => $this->pageRepository,
            ]
        );
    }

    public function testSaveAction()
    {
        $postData = [
            'title' => '"><img src=y onerror=prompt(document.domain)>;',
            'identifier' => 'unique_title_123',
            'stores' => ['0'],
            'is_active' => true,
            'content' => '"><script>alert("cookie: "+document.cookie)</script>'
        ];

        $filteredPostData = [
            'title' => '&quot;&gt;&lt;img src=y onerror=prompt(document.domain)&gt;;',
            'identifier' => 'unique_title_123',
            'stores' => ['0'],
            'is_active' => true,
            'content' => '&quot;&gt;&lt;script&gt;alert(&quot;cookie: &quot;+document.cookie)&lt;/script&gt;'
        ];

        $params = [
            ['page_id', null, $this->pageId],
            ['back', null, false],
        ];

        $this->dataProcessorMock->expects($this->any())
            ->method('filter')
            ->with($postData)
            ->willReturn($filteredPostData);

        $this->processRequest($postData, $params);
        /** @var \Magento\Cms\Model\Page|\PHPUnit_Framework_MockObject_MockObject $cmsPage */
        $page = $this->getPageMock();
        $this->pageRepository->expects($this->once())->method('save')->with($page);

        $this->dataPersistorMock->expects($this->any())
            ->method('clear')
            ->with('cms_page');

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccess')
            ->with(__('You saved the page.'));

        $this->resultRedirect->expects($this->atLeastOnce())->method('setPath')->with('*/*/') ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->saveController->execute());
    }

    public function testSaveActionWithoutData()
    {
        $this->requestMock->expects($this->any())->method('getPostValue')->willReturn(false);
        $this->resultRedirect->expects($this->atLeastOnce())->method('setPath')->with('*/*/') ->willReturnSelf();
        $this->assertSame($this->resultRedirect, $this->saveController->execute());
    }

    public function testSaveAndContinue()
    {
        $postData = ['page_id' => $this->pageId];
        $params = [
            ['page_id', null, $this->pageId],
            ['back', null, true],
        ];

        $this->processRequest($postData, $params);

        $this->dataProcessorMock->expects($this->any())
            ->method('filter')
            ->willReturnArgument(0);
        /** @var \Magento\Cms\Model\Page|\PHPUnit_Framework_MockObject_MockObject $cmsPage */
        $page = $this->getPageMock();

        $this->pageRepository->expects($this->once())->method('save')->with($page);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccess')
            ->with(__('You saved the page.'));
        $this->dataPersistorMock->expects($this->any())
            ->method('clear')
            ->with('cms_page');

        $this->resultRedirect->expects($this->atLeastOnce())
            ->method('setPath')
            ->with('*/*/edit', ['page_id' => $this->pageId])
            ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->saveController->execute());
    }

    public function testSaveActionThrowsException()
    {
        $postData = ['page_id' => $this->pageId];
        $params = [
            ['page_id', null, $this->pageId],
            ['back', null, true],
        ];

        $this->processRequest($postData, $params);

        $this->dataProcessorMock->expects($this->any())
            ->method('filter')
            ->willReturnArgument(0);
        /** @var \Magento\Cms\Model\Page|\PHPUnit_Framework_MockObject_MockObject $cmsPage */
        $page = $this->getPageMock();
        $this->pageRepository->expects($this->once())
            ->method('save')
            ->with($page)
            ->willThrowException(new \Exception('Error message.'));

        $this->messageManagerMock->expects($this->never())
            ->method('addSuccess');
        $this->messageManagerMock->expects($this->once())
            ->method('addException');

        $this->dataPersistorMock->expects($this->any())
            ->method('set')
            ->with('cms_page', ['page_id' => $this->pageId]);

        $this->resultRedirect->expects($this->atLeastOnce())
            ->method('setPath')
            ->with('*/*/edit', ['page_id' => $this->pageId])
            ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->saveController->execute());
    }

    /**
     * Create Cms Page Mock.
     *
     * @return \Magento\Cms\Model\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getPageMock()
    {
        $page = $this->getMockBuilder(\Magento\Cms\Model\Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($page);

        $page->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $page->expects($this->any())
            ->method('getId')
            ->willReturn(true);
        $page->expects($this->once())->method('setData');

        return $page;
    }

    /**
     * Process save page action request.
     *
     * @param array $postData
     * @param array $params
     * @return void
     */
    private function processRequest($postData, $params)
    {
        $this->requestMock->expects($this->any())->method('getPostValue')->willReturn($postData);
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturnMap($params);
    }
}
