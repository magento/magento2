<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Page;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Controller\Adminhtml\Page\PostDataProcessor;
use Magento\Cms\Controller\Adminhtml\Page\Save;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends TestCase
{
    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var PostDataProcessor|MockObject
     */
    private $dataProcessorMock;

    /**
     * @var DataPersistorInterface|MockObject
     */
    private $dataPersistorMock;

    /**
     * @var RedirectFactory|MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var Redirect|MockObject
     */
    private $resultRedirect;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|MockObject
     */
    private $eventManagerMock;

    /**
     * @var PageFactory|MockObject
     */
    private $pageFactory;

    /**
     * @var PageRepositoryInterface|MockObject
     */
    private $pageRepository;

    /**
     * @var Save
     */
    private $saveController;

    /**
     * @var int
     */
    private $pageId = 1;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->resultRedirectFactory = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->resultRedirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultRedirect);
        $this->dataProcessorMock = $this->getMockBuilder(
            PostDataProcessor::class
        )->onlyMethods(['filter'])->disableOriginalConstructor()
            ->getMock();
        $this->dataPersistorMock = $this->getMockBuilder(DataPersistorInterface::class)
            ->getMock();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->addMethods(['getPostValue'])
            ->onlyMethods(['getParam'])
            ->getMockForAbstractClass();
        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();
        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->onlyMethods(['dispatch'])
            ->getMockForAbstractClass();
        $this->pageFactory = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->pageRepository = $this->getMockBuilder(PageRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $context->method('getRequest')->willReturn($this->requestMock);
        $context->method('getMessageManager')->willReturn($this->messageManagerMock);
        $context->method('getEventManager')->willReturn($this->eventManagerMock);
        $context->method('getResultRedirectFactory')->willReturn($this->resultRedirectFactory);
        $this->saveController = new Save(
            $context,
            $this->dataProcessorMock,
            $this->dataPersistorMock,
            $this->pageFactory,
            $this->pageRepository
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
            'back' => 'close',
        ];

        $filteredPostData = [
            'title' => '&quot;&gt;&lt;img src=y onerror=prompt(document.domain)&gt;;',
            'identifier' => 'unique_title_123',
            'stores' => ['0'],
            'is_active' => true,
            'content' => '&quot;&gt;&lt;script&gt;alert(&quot;cookie: &quot;+document.cookie)&lt;/script&gt;',
            'back' => 'close',
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
        $page = $this->getMockBuilder(Page::class)
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

        $this->resultRedirect->expects($this->atLeastOnce())->method('setPath')->with('*/*/')->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->saveController->execute());
    }

    public function testSaveActionWithoutData()
    {
        $this->requestMock->expects($this->any())->method('getPostValue')->willReturn(false);
        $this->resultRedirect->expects($this->atLeastOnce())->method('setPath')->with('*/*/')->willReturnSelf();
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

        $page = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pageFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($page);
        $this->pageRepository->expects($this->once())
            ->method('getById')
            ->with($this->pageId)
            ->willThrowException(new NoSuchEntityException(__('Error message')));
        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('This page no longer exists.'));
        $this->resultRedirect->expects($this->atLeastOnce())->method('setPath')->with('*/*/')->willReturnSelf();
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
            'back' => 'continue',
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
        $page = $this->getMockBuilder(Page::class)
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
        $page = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($page);

        $this->pageRepository->expects($this->once())->method('getById')->with($this->pageId)->willReturn($page);
        $page->expects($this->once())->method('setData');
        $this->pageRepository->expects($this->once())->method('save')->with($page)
            ->willThrowException(new \Error('Error message.'));

        $this->messageManagerMock->expects($this->never())
            ->method('addSuccessMessage');
        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with('Something went wrong while saving the page.');

        $this->dataPersistorMock->expects($this->any())
            ->method('set')
            ->with(
                'cms_page',
                [
                    'page_id' => $this->pageId,
                    'layout_update_xml' => null,
                    'custom_layout_update_xml' => null,
                ]
            );

        $this->resultRedirect->expects($this->atLeastOnce())
            ->method('setPath')
            ->with('*/*/edit', ['page_id' => $this->pageId])
            ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->saveController->execute());
    }
}
