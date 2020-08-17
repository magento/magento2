<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Test\Unit\Controller\Url\Rewrite;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite\InlineEdit;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewrite as UrlRewriteResource;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteFactory as UrlRewriteResourceFactory;
use Magento\UrlRewrite\Model\UrlRewrite;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Url rewrite inline edit action unit test class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InlineEditTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var JsonFactory|MockObject
     */
    private $jsonFactory;

    /**
     * @var Json|MockObject
     */
    private $resultJson;

    /**
     * @var UrlRewrite|MockObject
     */
    private $urlRewrite;

    /**
     * @var UrlRewriteFactory|MockObject
     */
    private $urlRewriteFactory;

    /**
     * @var UrlRewriteResource|MockObject
     */
    private $urlRewriteResource;

    /**
     * @var urlRewriteResourceFactory|MockObject
     */
    private $urlRewriteResourceFactory;

    /**
     * @var InlineEdit|MockObject
     */
    private $inlineEditController;

    /**
     * SetUp method
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->request = $this->getMockForAbstractClass(
            RequestInterface::class,
            [],
            '',
            false
        );

        $this->contextMock = $this->objectManager->getObject(
            Context::class,
            [
                'request' => $this->request
            ]
        );

        $this->resultJson = $this->createMock(Json::class);
        $this->jsonFactory = $this->createPartialMock(
            JsonFactory::class,
            ['create']
        );

        $this->urlRewrite = $this->createPartialMock(
            UrlRewrite::class,
            ['addData', 'getId']
        );
        $this->urlRewriteFactory = $this->createPartialMock(
            UrlRewriteFactory::class,
            ['create']
        );

        $this->urlRewriteResource = $this->createPartialMock(
            UrlRewriteResource::class,
            ['load', 'save']
        );
        $this->urlRewriteResourceFactory = $this->createPartialMock(
            urlRewriteResourceFactory::class,
            ['create']
        );

        $this->inlineEditController = $this->objectManager->getObject(
            InlineEdit::class,
            [
                'context' => $this->contextMock,
                'urlRewriteFactory' => $this->urlRewriteFactory,
                'urlRewriteResourceFactory' => $this->urlRewriteResourceFactory,
                'jsonFactory' => $this->jsonFactory
            ]
        );
    }

    /**
     * Prepare mocks for tests
     *
     * @return void
     */
    private function prepareMocksWithParamsForTestExecute(): void
    {
        $postData = [
            1 => [
                'request_path' => 'category-1.html',
                'redirect_type' => 0,
                'url_rewrite_id' => 1
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

        $this->jsonFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJson);

        $this->urlRewriteResourceFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->urlRewriteResource);

        $this->urlRewriteResource->expects($this->once())
            ->method('load')
            ->with($this->urlRewrite, 1)
            ->willReturn($this->urlRewrite);

        $this->urlRewriteFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->urlRewrite);

        $this->urlRewrite->expects($this->once())
            ->method('addData')
            ->with(array_shift($postData))
            ->willReturnSelf();
    }

    /**
     * Execute test with runtime exception
     *
     * @return void
     */
    public function testExecuteWithRuntimeException(): void
    {
        $this->prepareMocksWithParamsForTestExecute();
        $this->urlRewrite->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->urlRewriteResource->expects($this->once())
            ->method('save')
            ->with($this->urlRewrite)
            ->willThrowException(new \RuntimeException('RuntimeException'));
        $this->resultJson->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'messages' => [
                        '[Url rewrite ID: 1] RuntimeException'
                    ],
                    'error' => true
                ]
            )
            ->willReturnSelf();

        $this->assertSame($this->resultJson, $this->inlineEditController->execute());
    }

    /**
     * Execute test with exception
     *
     * @return void
     */
    public function testExecuteWithException(): void
    {
        $this->prepareMocksWithParamsForTestExecute();
        $this->urlRewrite->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->urlRewriteResource->expects($this->once())
            ->method('save')
            ->with($this->urlRewrite)
            ->willThrowException(new \Exception('Exception'));
        $this->resultJson->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'messages' => [
                        '[Url rewrite ID: 1] Something went wrong while saving the url rewrite.'
                    ],
                    'error' => true
                ]
            )
            ->willReturnSelf();

        $this->assertSame($this->resultJson, $this->inlineEditController->execute());
    }

    /**
     * Execute test without post data
     *
     * @return void
     */
    public function testExecuteWithoutData(): void
    {
        $this->jsonFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJson);
        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['isAjax', null, true],
                    ['items', [], []]
                ]
            );
        $this->resultJson->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'messages' => [
                        __('Please correct the data sent.')
                    ],
                    'error' => true
                ]
            )
            ->willReturnSelf();

        $this->assertSame($this->resultJson, $this->inlineEditController->execute());
    }

    /**
     * Execute test without exceptions
     *
     * @return void
     */
    public function testExecuteAction(): void
    {
        $this->prepareMocksWithParamsForTestExecute();
        $this->urlRewriteResource->expects($this->once())
            ->method('save')
            ->with($this->urlRewrite)
            ->willReturnSelf();
        $this->resultJson->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'messages' => [],
                    'error' => false
                ]
            )
            ->willReturnSelf();

        $this->assertSame(
            $this->resultJson,
            $this->inlineEditController->execute()
        );
    }
}
