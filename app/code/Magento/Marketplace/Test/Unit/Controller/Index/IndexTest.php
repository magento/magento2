<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Marketplace\Test\Unit\Controller\Index;

use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Marketplace\Controller\Adminhtml\Index\Index;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    /**
     * @var MockObject|Index
     */
    private $indexControllerMock;

    protected function setUp(): void
    {
        $this->indexControllerMock = $this->createPartialMock(Index::class, ['getResultPageFactory']);
    }

    /**
     * @covers \Magento\Marketplace\Controller\Adminhtml\Index\Index::execute
     */
    public function testExecute()
    {
        $pageMock = $this->getMockBuilder(Page::class)
            ->addMethods(['setActiveMenu', 'addBreadcrumb'])
            ->onlyMethods(['getConfig'])
            ->disableOriginalConstructor()
            ->getMock();
        $pageMock->expects($this->once())
            ->method('setActiveMenu');
        $pageMock->expects($this->once())
            ->method('addBreadcrumb');

        $resultPageFactoryMock = $this->createPartialMock(PageFactory::class, ['create']);

        $resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($pageMock);

        $this->indexControllerMock->expects($this->once())
            ->method('getResultPageFactory')
            ->willReturn($resultPageFactoryMock);

        $titleMock = $this->createPartialMock(Title::class, ['prepend']);
        $titleMock->expects($this->once())
            ->method('prepend');
        $configMock = $this->createPartialMock(Config::class, ['getTitle']);
        $configMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($titleMock);
        $pageMock->expects($this->once())
            ->method('getConfig')
            ->willReturn($configMock);

        $this->indexControllerMock->execute();
    }
}
