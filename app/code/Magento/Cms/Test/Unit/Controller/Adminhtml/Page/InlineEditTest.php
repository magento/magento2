<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Page;

use Magento\Backend\App\Action\Context;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Controller\Adminhtml\Page\InlineEdit;
use Magento\Cms\Controller\Adminhtml\Page\PostDataProcessor;
use Magento\Cms\Model\Page;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\Collection;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InlineEditTest extends TestCase
{
    /** @var RequestInterface|MockObject */
    protected $request;

    /** @var ManagerInterface|MockObject */
    protected $messageManager;

    /** @var MessageInterface|MockObject */
    protected $message;

    /** @var Collection|MockObject */
    protected $messageCollection;

    /** @var \Magento\Cms\Model\Page|MockObject */
    protected $cmsPage;

    /** @var Context|MockObject */
    protected $context;

    /** @var PostDataProcessor|MockObject */
    protected $dataProcessor;

    /** @var PageRepositoryInterface|MockObject */
    protected $pageRepository;

    /** @var JsonFactory|MockObject */
    protected $jsonFactory;

    /** @var Json|MockObject */
    protected $resultJson;

    /** @var InlineEdit */
    protected $controller;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->request = $this->getMockForAbstractClass(RequestInterface::class);
        $this->messageManager = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->messageCollection = $this->createMock(Collection::class);
        $this->message = $this->getMockForAbstractClass(MessageInterface::class);
        $this->cmsPage = $this->createMock(Page::class);
        $this->context = $helper->getObject(
            Context::class,
            [
                'request' => $this->request,
                'messageManager' => $this->messageManager
            ]
        );
        $this->dataProcessor = $this->createMock(PostDataProcessor::class);
        $this->pageRepository = $this->getMockForAbstractClass(PageRepositoryInterface::class);
        $this->resultJson = $this->createMock(Json::class);
        $this->jsonFactory = $this->createPartialMock(
            JsonFactory::class,
            ['create']
        );
        $this->controller = new InlineEdit(
            $this->context,
            $this->dataProcessor,
            $this->pageRepository,
            $this->jsonFactory
        );
    }

    public function prepareMocksForTestExecute()
    {
        $postData = [
            1 => [
                'title' => '404 Not Found',
                'identifier' => 'no-route',
                'custom_theme' => '1',
                'custom_root_template' => '2'
            ]
        ];
        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['isAjax', null, true],
                    ['items', [], $postData]
                ]
            );
        $this->pageRepository->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($this->cmsPage);
        $this->dataProcessor->expects($this->once())
            ->method('filter')
            ->with($postData[1])
            ->willReturnArgument(0);
        $this->messageManager->expects($this->once())
            ->method('getMessages')
            ->with(true)
            ->willReturn($this->messageCollection);
        $this->messageCollection
            ->expects($this->once())
            ->method('getItems')
            ->willReturn([$this->message]);
        $this->message->expects($this->once())
            ->method('getText')
            ->willReturn('Error message');
        $this->cmsPage->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn('1');
        $this->cmsPage->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn(
                [
                    'layout' => '1column',
                    'identifier' => 'test-identifier'
                ]
            );
        $this->cmsPage->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'layout' => '1column',
                    'title' => '404 Not Found',
                    'identifier' => 'no-route',
                    'custom_theme' => '1',
                    'custom_root_template' => '2'
                ]
            );
        $this->jsonFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJson);
    }

    public function testExecuteWithLocalizedException()
    {
        $this->prepareMocksForTestExecute();
        $this->pageRepository->expects($this->once())
            ->method('save')
            ->with($this->cmsPage)
            ->willThrowException(new LocalizedException(__('LocalizedException')));
        $this->resultJson->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'messages' => [
                        '[Page ID: 1] Error message',
                        '[Page ID: 1] LocalizedException'
                    ],
                    'error' => true
                ]
            )
            ->willReturnSelf();

        $this->assertSame($this->resultJson, $this->controller->execute());
    }

    public function testExecuteWithRuntimeException()
    {
        $this->prepareMocksForTestExecute();
        $this->pageRepository->expects($this->once())
            ->method('save')
            ->with($this->cmsPage)
            ->willThrowException(new \RuntimeException('RuntimeException'));
        $this->resultJson->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'messages' => [
                        '[Page ID: 1] Error message',
                        '[Page ID: 1] RuntimeException'
                    ],
                    'error' => true
                ]
            )
            ->willReturnSelf();

        $this->assertSame($this->resultJson, $this->controller->execute());
    }

    public function testExecuteWithException()
    {
        $this->prepareMocksForTestExecute();
        $this->pageRepository->expects($this->once())
            ->method('save')
            ->with($this->cmsPage)
            ->willThrowException(new \Exception('Exception'));
        $this->resultJson->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'messages' => [
                        '[Page ID: 1] Error message',
                        '[Page ID: 1] Something went wrong while saving the page.'
                    ],
                    'error' => true
                ]
            )
            ->willReturnSelf();

        $this->assertSame($this->resultJson, $this->controller->execute());
    }

    public function testExecuteWithoutData()
    {
        $this->jsonFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJson);
        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['items', [], []],
                    ['isAjax', null, true]
                ]
            );
        $this->resultJson->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'messages' => [
                        'Please correct the data sent.'
                    ],
                    'error' => true
                ]
            )
            ->willReturnSelf();

        $this->assertSame($this->resultJson, $this->controller->execute());
    }

    public function testSetCmsPageData()
    {
        $extendedPageData = [
            'page_id' => '2',
            'title' => 'Home Page',
            'page_layout' => '1column',
            'identifier' => 'home',
            'content_heading' => 'Home Page',
            'content' => 'CMS homepage content goes here.',
            'is_active' => '1',
            'sort_order' => '1',
            'custom_theme' => '3',
            'store_id' => ['0']
        ];
        $pageData = [
            'page_id' => '2',
            'title' => 'Home Page',
            'page_layout' => '1column',
            'identifier' => 'home',
            'is_active' => '1',
            'custom_theme' => '3',
        ];
        $getData = [
            'page_id' => '2',
            'title' => 'Home Page',
            'page_layout' => '1column',
            'identifier' => 'home',
            'content_heading' => 'Home Page',
            'content' => 'CMS homepage content goes here.',
            'is_active' => '1',
            'sort_order' => '1',
            'custom_theme' => '3',
            'custom_root_template' => '1column',
            'store_id' => ['0']
        ];
        $mergedData = [
            'page_id' => '2',
            'title' => 'Home Page',
            'page_layout' => '1column',
            'identifier' => 'home',
            'content_heading' => 'Home Page',
            'content' => 'CMS homepage content goes here.',
            'is_active' => '1',
            'sort_order' => '1',
            'custom_theme' => '3',
            'custom_root_template' => '1column',
            'store_id' => ['0']
        ];
        $this->cmsPage->expects($this->once())->method('getData')->willReturn($getData);
        $this->cmsPage->expects($this->once())->method('setData')->with($mergedData)->willReturnSelf();
        $this->assertSame(
            $this->controller,
            $this->controller->setCmsPageData($this->cmsPage, $extendedPageData, $pageData)
        );
    }
}
