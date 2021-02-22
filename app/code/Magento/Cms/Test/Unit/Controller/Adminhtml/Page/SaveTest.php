<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Page;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Cms\Controller\Adminhtml\Page\PostDataProcessor|\PHPUnit\Framework\MockObject\MockObject
     */
    private $dataProcessorMock;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $dataPersistorMock;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resultRedirect;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $messageManagerMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $eventManagerMock;

    /**
     * @var \Magento\Cms\Model\PageFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $pageFactory;

    /**
     * @var \Magento\Cms\Api\PageRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
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

    protected function setUp(): void
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
        )->setMethods(['filter'])->disableOriginalConstructor()->getMock();
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
                'pageRepository' => $this->pageRepository
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
            'content' => '"><script>alert("cookie: "+document.cookie)</script>',
            'back' => 'close'
        ];

        $filteredPostData = [
            'title' => '&quot;&gt;&lt;img src=y onerror=prompt(document.domain)&gt;;',
            'identifier' => 'unique_title_123',
            'stores' => ['0'],
            'is_active' => true,
            'content' => '&quot;&gt;&lt;script&gt;alert(&quot;cookie: &quot;+document.cookie)&lt;/script&gt;',
            'back' => 'close'
        ];

        $this->dataProcessorMock->expects($this->any())
            ->method('filter')
            ->with($postData)
            ->willReturn($filteredPostData);

        $this->requestMock->expects($this->any())->method('getPostValue')->willReturn($postData);
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['page_id', null, $this->pageId],
                    ['back', null, false],
                ]
            );
        $page = $this->getMockBuilder(\Magento\Cms\Model\Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($page);

        $this->pageRepository->expects($this->once())->method('getById')->with($this->pageId)->willReturn($page);
        $page->expects($this->once())->method('setData');
        $page->method('getId')->willReturn($this->pageId);
        $this->pageRepository->expects($this->once())->method('save')->with($page);

        $this->dataPersistorMock->expects($this->any())
            ->method('clear')
            ->with('cms_page');

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
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

    public function testSaveActionNoId()
    {
        $this->requestMock->expects($this->any())->method('getPostValue')->willReturn(['page_id' => 1]);
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['page_id', null, 1],
                    ['back', null, 'close'],
                ]
            );

        $page = $this->getMockBuilder(\Magento\Cms\Model\Page::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pageFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($page);
        $this->pageRepository->expects($this->once())
            ->method('getById')
            ->with($this->pageId)
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException(__('Error message')));
        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('This page no longer exists.'));
        $this->resultRedirect->expects($this->atLeastOnce())->method('setPath')->with('*/*/') ->willReturnSelf();
        $this->assertSame($this->resultRedirect, $this->saveController->execute());
    }

    public function testSaveAndContinue()
    {
        $postData = [
            'title' => '"><img src=y onerror=prompt(document.domain)>;',
            'identifier' => 'unique_title_123',
            'stores' => ['0'],
            'is_active' => true,
            'content' => '"><script>alert("cookie: "+document.cookie)</script>',
            'back' => 'continue'
        ];
        $this->requestMock->expects($this->any())->method('getPostValue')->willReturn($postData);
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['page_id', null, $this->pageId],
                    ['back', null, 'continue'],
                ]
            );

        $this->dataProcessorMock->expects($this->any())
            ->method('filter')
            ->willReturnArgument(0);
        $page = $this->getMockBuilder(\Magento\Cms\Model\Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->method('getId')->willReturn(1);
        $this->pageFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($page);

        $this->pageRepository->expects($this->once())->method('getById')->with($this->pageId)->willReturn($page);
        $page->expects($this->once())->method('setData');
        $this->pageRepository->expects($this->once())->method('save')->with($page);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
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
        $this->requestMock->expects($this->any())->method('getPostValue')->willReturn(['page_id' => $this->pageId]);
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['page_id', null, $this->pageId],
                    ['back', null, true],
                ]
            );

        $this->dataProcessorMock->expects($this->any())
            ->method('filter')
            ->willReturnArgument(0);
        $page = $this->getMockBuilder(\Magento\Cms\Model\Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($page);

        $this->pageRepository->expects($this->once())->method('getById')->with($this->pageId)->willReturn($page);
        $page->expects($this->once())->method('setData');
        $this->pageRepository->expects($this->once())->method('save')->with($page)
            ->willThrowException(new \Exception('Error message.'));

        $this->messageManagerMock->expects($this->never())
            ->method('addSuccessMessage');
        $this->messageManagerMock->expects($this->once())
            ->method('addExceptionMessage');

        $this->dataPersistorMock->expects($this->any())
            ->method('set')
            ->with(
                'cms_page',
                [
                    'page_id' => $this->pageId,
                    'layout_update_xml' => null,
                    'custom_layout_update_xml' => null
                ]
            );

        $this->resultRedirect->expects($this->atLeastOnce())
            ->method('setPath')
            ->with('*/*/edit', ['page_id' => $this->pageId])
            ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->saveController->execute());
    }
}
