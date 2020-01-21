<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlPlayground\Test\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\State;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\GraphqlPlayground\Controller\Index\Index;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class IndexTest
 *
 * @package Magento\GraphQlPlayground\Test\Controller\Index
 */
class IndexTest extends TestCase
{
    /**
     * @var State | MockObject
     */
    private $appState;

    /**
     * @var Context | MockObject
     */
    private $context;

    /**
     * @var PageFactory | MockObject
     */
    private $pageFactory;

    /**
     * @var LoggerInterface | MockObject
     */
    private $logger;

    /**
     * @var Index
     */
    private $indexController;

    /**
     * @var MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var Page | MockObject
     */
    private $page;

    /**
     * Test setup
     */
    public function setup(): void
    {
        $this->appState = $this->createMock(State::class);
        $this->context = $this->createMock(Context::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->pageFactory = $this->createMock(PageFactory::class);
        $this->resultRedirectFactory = $this->createMock(RedirectFactory::class);
        $this->page = $this->createMock(Page::class);
        $this->pageFactory
            ->method('create')
            ->willReturn($this->page);
        $this->indexController = new Index(
            $this->context,
            $this->pageFactory,
            $this->appState,
            $this->logger
        );
    }

    /**
     * Test No Redirection on Developer Mode
     */
    public function testReturnPageOnDeveloperMode(): void
    {
        $this->appState->method('getAreaCode')->willReturn(State::MODE_DEVELOPER);
        $this->assertInstanceOf(Page::class, $this->indexController->execute());
    }

    /**
     * Test No Redirection on Default Mode
     */
    public function testReturnPageOnDefaultMode(): void
    {
        $this->appState->method('getAreaCode')->willReturn(State::MODE_DEFAULT);
        $this->assertInstanceOf(Page::class, $this->indexController->execute());
    }
}
