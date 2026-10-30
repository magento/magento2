<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Model\Page\Service;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\Page\CustomLayoutManagerInterface;
use Magento\Cms\Model\Page\Service\PageService;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for PageService
 */
class PageServiceTest extends TestCase
{
    /**
     * @var PageRepositoryInterface|MockObject
     */
    private $pageRepositoryMock;

    /**
     * @var PageFactory|MockObject
     */
    private $pageFactoryMock;

    /**
     * @var CustomLayoutManagerInterface|MockObject
     */
    private $customLayoutManagerMock;

    /**
     * @var PageService
     */
    private $pageService;

    /**
     * @var PageInterface|MockObject
     */
    private $pageMock;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        $this->pageRepositoryMock = $this->createMock(PageRepositoryInterface::class);
        $this->pageFactoryMock = $this->createMock(PageFactory::class);
        $this->customLayoutManagerMock = $this->createMock(CustomLayoutManagerInterface::class);
        $this->pageMock = $this->createMock(PageInterface::class);

        $this->pageService = new PageService(
            $this->pageRepositoryMock,
            $this->pageFactoryMock,
            $this->customLayoutManagerMock
        );
    }

    /**
     * Test getPageById returns existing page when page exists
     */
    public function testGetPageByIdReturnsExistingPage(): void
    {
        $pageId = 1;

        $this->pageRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($pageId)
            ->willReturn($this->pageMock);

        $this->pageFactoryMock->expects($this->never())
            ->method('create');

        $result = $this->pageService->getPageById($pageId);

        $this->assertSame($this->pageMock, $result);
    }

    /**
     * Test getPageById returns new page instance when page doesn't exist
     */
    public function testGetPageByIdReturnsNewPageWhenNotExists(): void
    {
        $pageId = 999;
        $newPageMock = $this->createMock(PageInterface::class);

        $this->pageRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($pageId)
            ->willThrowException(new NoSuchEntityException(__('Page not found')));

        $this->pageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($newPageMock);

        $result = $this->pageService->getPageById($pageId);

        $this->assertSame($newPageMock, $result);
    }

    /**
     * Test getPageById handles LocalizedException and returns new page
     */
    public function testGetPageByIdHandlesLocalizedException(): void
    {
        $pageId = 1;
        $newPageMock = $this->createMock(PageInterface::class);

        $this->pageRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($pageId)
            ->willThrowException(new LocalizedException(__('Some localized error')));

        $this->pageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($newPageMock);

        $result = $this->pageService->getPageById($pageId);

        $this->assertSame($newPageMock, $result);
    }

    /**
     * Test createPageFactory returns new page instance
     */
    public function testCreatePageFactory(): void
    {
        $newPageMock = $this->createMock(PageInterface::class);

        $this->pageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($newPageMock);

        $result = $this->pageService->createPageFactory();

        $this->assertSame($newPageMock, $result);
    }

    /**
     * Test fetchAvailableCustomLayouts returns available layouts
     */
    public function testFetchAvailableCustomLayouts(): void
    {
        $expectedLayouts = [
            'layout1.xml',
            'layout2.xml',
            'custom_layout.xml'
        ];

        $this->customLayoutManagerMock->expects($this->once())
            ->method('fetchAvailableFiles')
            ->with($this->pageMock)
            ->willReturn($expectedLayouts);

        $result = $this->pageService->fetchAvailableCustomLayouts($this->pageMock);

        $this->assertSame($expectedLayouts, $result);
    }

    /**
     * Test fetchAvailableCustomLayouts returns empty array when no layouts available
     */
    public function testFetchAvailableCustomLayoutsReturnsEmptyArray(): void
    {
        $this->customLayoutManagerMock->expects($this->once())
            ->method('fetchAvailableFiles')
            ->with($this->pageMock)
            ->willReturn([]);

        $result = $this->pageService->fetchAvailableCustomLayouts($this->pageMock);

        $this->assertSame([], $result);
    }
}
