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
        $this->indexControllerMock = $this->getControllerIndexMock(['getResultPageFactory']);
    }

    /**
     * @covers \Magento\Marketplace\Controller\Adminhtml\Index\Index::execute
     */
    public function testExecute()
    {
        $pageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConfig'])
            ->addMethods(['setActiveMenu', 'addBreadcrumb'])
            ->getMock();
        $pageMock->expects($this->once())->method('setActiveMenu');
        $pageMock->expects($this->once())->method('addBreadcrumb');

        $resultPageFactoryMock = $this->getResultPageFactoryMock(['create']);

        $resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($pageMock);

        $this->indexControllerMock->expects($this->once())
            ->method('getResultPageFactory')
            ->willReturn($resultPageFactoryMock);

        $titleMock = $this->getTitleMock(['prepend']);
        $titleMock->expects($this->once())
            ->method('prepend');
        $configMock =  $this->getConfigMock(['getTitle']);
        $configMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($titleMock);
        $pageMock->expects($this->once())
            ->method('getConfig')
            ->willReturn($configMock);

        $this->indexControllerMock->execute();
    }

    /**
     * Gets index controller mock
     *
     * @return MockObject|Index
     */
    public function getControllerIndexMock($methods = null)
    {
        return $this->createPartialMock(Index::class, $methods);
    }

    /**
     * @return MockObject|PageFactory
     */
    public function getResultPageFactoryMock($methods = null)
    {
        return $this->createPartialMock(PageFactory::class, $methods);
    }

    /**
     * @return MockObject|Config
     */
    public function getConfigMock($methods = null)
    {
        return $this->createPartialMock(Config::class, $methods);
    }

    /**
     * @return MockObject|Title
     */
    public function getTitleMock($methods = null)
    {
        return $this->createPartialMock(Title::class, $methods);
    }

    /**
     * @param array $addMethods Methods that are missing in original class
     * @return MockObject|Title
     */
    public function getPageMock(array $addMethods = [])
    {
        return $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->addMethods($addMethods)
            ->getMock();
    }
}
