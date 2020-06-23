<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Controller\Block;

use Magento\Backend\App\Action\Context;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Controller\Adminhtml\Block\InlineEdit;
use Magento\Cms\Model\Block;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InlineEditTest extends TestCase
{
    /** @var RequestInterface|MockObject */
    protected $request;

    /** @var Block|MockObject */
    protected $cmsBlock;

    /** @var Context|MockObject */
    protected $context;

    /** @var BlockRepositoryInterface|MockObject */
    protected $blockRepository;

    /** @var JsonFactory|MockObject */
    protected $jsonFactory;

    /** @var Json|MockObject */
    protected $resultJson;

    /** @var InlineEdit */
    protected $controller;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->request = $this->getMockForAbstractClass(
            RequestInterface::class,
            [],
            '',
            false
        );
        $this->cmsBlock = $this->createMock(Block::class);
        $this->context = $helper->getObject(
            Context::class,
            [
                'request' => $this->request
            ]
        );
        $this->blockRepository = $this->getMockForAbstractClass(
            BlockRepositoryInterface::class,
            [],
            '',
            false
        );
        $this->resultJson = $this->createMock(Json::class);
        $this->jsonFactory = $this->createPartialMock(
            JsonFactory::class,
            ['create']
        );
        $this->controller = new InlineEdit(
            $this->context,
            $this->blockRepository,
            $this->jsonFactory
        );
    }

    public function prepareMocksForTestExecute()
    {
        $postData = [
            1 => [
                'title' => 'Catalog Events Lister',
                'identifier' => 'Catalog Events Lister'
            ]
        ];

        $this->request->expects($this->at(0))
            ->method('getParam')
            ->with('isAjax')
            ->willReturn(true);
        $this->request->expects($this->at(1))
            ->method('getParam')
            ->with('items', [])
            ->willReturn($postData);
        $this->blockRepository->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($this->cmsBlock);
        $this->cmsBlock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn('1');
        $this->cmsBlock->expects($this->once())
            ->method('getData')
            ->willReturn([
                'identifier' => 'test-identifier'
            ]);
        $this->cmsBlock->expects($this->once())
            ->method('setData')
            ->with([
                'title' => 'Catalog Events Lister',
                'identifier' => 'Catalog Events Lister'
            ]);
        $this->jsonFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJson);
    }

    public function testExecuteWithException()
    {
        $this->prepareMocksForTestExecute();
        $this->blockRepository->expects($this->once())
            ->method('save')
            ->with($this->cmsBlock)
            ->willThrowException(new \Exception('Exception'));
        $this->resultJson->expects($this->once())
            ->method('setData')
            ->with([
                'messages' => [
                    '[Block ID: 1] Exception'
                ],
                'error' => true
            ])
            ->willReturnSelf();

        $this->controller->execute();
    }

    public function testExecuteWithoutData()
    {
        $this->request->expects($this->at(0))
            ->method('getParam')
            ->with('isAjax')
            ->willReturn(true);
        $this->request->expects($this->at(1))
            ->method('getParam')
            ->with('items', [])
            ->willReturn([]);
        $this->jsonFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJson);
        $this->resultJson->expects($this->once())
            ->method('setData')
            ->with([
                'messages' => [
                    'Please correct the data sent.'
                ],
                'error' => true
            ])
            ->willReturnSelf();

        $this->controller->execute();
    }
}
