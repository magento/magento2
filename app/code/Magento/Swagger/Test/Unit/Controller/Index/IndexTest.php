<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Swagger\Test\Unit\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\Result\PageFactory;
use Magento\Swagger\Controller\Index\Index;
use Magento\Swagger\Model\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    /**
     * @var PageConfig|MockObject
     */
    private $pageConfigMock;

    /**
     * @var PageFactory|MockObject
     */
    private $resultPageFactory;

    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var Index
     */
    private $indexAction;

    protected function setUp(): void
    {
        /** @var MockObject|Context $pageConfigMock */
        $contextMock = $this->createMock(Context::class);

        /** @var MockObject|PageConfig $pageConfigMock */
        $this->pageConfigMock = $this->getMockBuilder(PageConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|PageFactory $resultPageFactory */
        $this->resultPageFactory = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->config = self::getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->indexAction = new Index(
            $contextMock,
            $this->pageConfigMock,
            $this->resultPageFactory,
            $this->config
        );
    }

    /**
     * @return void
     * @throws NotFoundException
     */
    public function testExecute(): void
    {
        $this->pageConfigMock->expects($this->once())
            ->method('addBodyClass')
            ->with('swagger-section');
        $this->resultPageFactory->expects($this->once())
            ->method('create');

        $this->indexAction->execute();
    }

    public function testDispatchRejectsWhenDisabled()
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Page not found.');

        $request = self::getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->config->method('isEnabled')
            ->willReturn(false);
        $this->indexAction->dispatch($request);
    }

    /**
     * @return void
     * @throws NotFoundException
     */
    public function testDispatchIsSuccessfulWhenEnabled(): void
    {
        $request = self::getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        // Assert that execute is called
        $request->expects($this->once())
            ->method('getFullActionName');
        $this->config->method('isEnabled')
            ->willReturn(true);

        $this->indexAction->dispatch($request);
    }
}
