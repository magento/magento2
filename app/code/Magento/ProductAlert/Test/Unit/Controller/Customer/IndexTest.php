<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Test\Unit\Controller\Customer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\Page\Title as PageTitle;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\ProductAlert\Controller\Customer\Index;
use Magento\ProductAlert\Helper\Data as ProductAlertHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\ProductAlert\Controller\Customer\Index
 */
class IndexTest extends TestCase
{
    /**
     * @var Index
     */
    private $controller;

    /**
     * @var PageFactory|MockObject
     */
    private $resultPageFactoryMock;

    /**
     * @var ProductAlertHelper|MockObject
     */
    private $productAlertHelperMock;

    /**
     * @var Page|MockObject
     */
    private $resultPageMock;

    /**
     * @var PageTitle|MockObject
     */
    private $pageTitleMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->resultPageFactoryMock = $this->createMock(PageFactory::class);
        $this->productAlertHelperMock = $this->createMock(ProductAlertHelper::class);
        $this->resultPageMock = $this->createMock(Page::class);
        $pageConfigMock = $this->createMock(PageConfig::class);
        $this->pageTitleMock = $this->createMock(PageTitle::class);
        $contextMock = $this->createMock(Context::class);
        $customerSessionMock = $this->createMock(Session::class);

        $this->resultPageMock->method('getConfig')->willReturn($pageConfigMock);
        $pageConfigMock->method('getTitle')->willReturn($this->pageTitleMock);
        $this->resultPageFactoryMock->method('create')->willReturn($this->resultPageMock);

        $this->controller = $objectManager->getObject(
            Index::class,
            [
                'context' => $contextMock,
                'customerSession' => $customerSessionMock,
                'resultPageFactory' => $this->resultPageFactoryMock,
                'productAlertHelper' => $this->productAlertHelperMock,
            ]
        );
    }

    public function testExecuteRendersPageWhenPriceAllowed(): void
    {
        $this->productAlertHelperMock->method('isPriceAlertAllowed')->willReturn(true);
        $this->productAlertHelperMock->method('isStockAlertAllowed')->willReturn(false);
        $this->pageTitleMock->expects($this->once())->method('set');

        $this->assertSame($this->resultPageMock, $this->controller->execute());
    }

    public function testExecuteRendersPageWhenStockAllowed(): void
    {
        $this->productAlertHelperMock->method('isPriceAlertAllowed')->willReturn(false);
        $this->productAlertHelperMock->method('isStockAlertAllowed')->willReturn(true);
        $this->pageTitleMock->expects($this->once())->method('set');

        $this->assertSame($this->resultPageMock, $this->controller->execute());
    }

    public function testExecuteThrowsNotFoundWhenBothDisabled(): void
    {
        $this->productAlertHelperMock->method('isPriceAlertAllowed')->willReturn(false);
        $this->productAlertHelperMock->method('isStockAlertAllowed')->willReturn(false);
        $this->expectException(NotFoundException::class);
        $this->controller->execute();
    }
}
