<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CmsGraphQl\Test\Unit\Model\Resolver\DataProvider;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\GetPageByIdentifierInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\CmsGraphQl\Model\Resolver\DataProvider\Page;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Widget\Model\Template\FilterEmulate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PageTest extends TestCase
{
    /**
     * @var PageRepositoryInterface|MockObject
     */
    private PageRepositoryInterface $pageRepositoryMock;

    /**
     * @var GetPageByIdentifierInterface|MockObject
     */
    private GetPageByIdentifierInterface $pageByIdentifierMock;

    /**
     * @var FilterEmulate|MockObject
     */
    private FilterEmulate $widgetFilterMock;

    /**
     * @var Page
     */
    private Page $page;

    protected function setUp(): void
    {
        $this->pageRepositoryMock = $this->createMock(PageRepositoryInterface::class);
        $this->widgetFilterMock = $this->createMock(FilterEmulate::class);
        $this->pageByIdentifierMock = $this->createMock(GetPageByIdentifierInterface::class);

        $this->page = new Page(
            $this->pageRepositoryMock,
            $this->widgetFilterMock,
            $this->pageByIdentifierMock
        );
    }

    /**
     * Test that page data is retrieved by id and all fields are returned when no ResolveInfo is provided
     *
     * @return void
     */
    public function testGetDataByPageIdReturnsAllFieldsWhenInfoIsNull(): void
    {
        $pageMock = $this->getActivePageMock();

        $this->pageRepositoryMock->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($pageMock);

        $this->pageByIdentifierMock->expects($this->never())
            ->method('execute');

        $this->widgetFilterMock->expects($this->once())
            ->method('filter')
            ->with('page content')
            ->willReturn('filtered page content');

        $result = $this->page->getDataByPageId(1);

        $this->assertSame($this->getExpectedPageData('filtered page content'), $result);
    }

    /**
     * Test that page data is retrieved by id and only the requested fields are returned
     *
     * @return void
     */
    public function testGetDataByPageIdReturnsOnlyRequestedFields(): void
    {
        $pageMock = $this->getActivePageMock();

        $this->pageRepositoryMock->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($pageMock);

        $this->pageByIdentifierMock->expects($this->never())
            ->method('execute');

        $this->widgetFilterMock->expects($this->never())
            ->method('filter');

        $infoMock = $this->getResolveInfoMock(['title' => 1]);

        $result = $this->page->getDataByPageId(1, $infoMock);

        $expected = $this->getExpectedPageData();
        unset($expected[PageInterface::CONTENT]);

        $this->assertSame($expected, $result);
    }

    /**
     * Test that content is included when explicitly requested via ResolveInfo field selection
     *
     * @return void
     */
    public function testGetDataByPageIdIncludesContentWhenRequested(): void
    {
        $pageMock = $this->getActivePageMock();

        $this->pageRepositoryMock->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($pageMock);

        $this->pageByIdentifierMock->expects($this->never())
            ->method('execute');

        $this->widgetFilterMock->expects($this->once())
            ->method('filter')
            ->with('page content')
            ->willReturn('filtered page content');

        $infoMock = $this->getResolveInfoMock(['title' => 1, 'content' => 1]);

        $result = $this->page->getDataByPageId(1, $infoMock);

        $this->assertSame($this->getExpectedPageData('filtered page content'), $result);
    }

    /**
     * Test that an exception is thrown when the page retrieved by id is inactive
     *
     * @return void
     */
    public function testGetDataByPageIdThrowsExceptionWhenPageIsInactive(): void
    {
        $pageMock = $this->getInactivePageMock();

        $this->pageRepositoryMock->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($pageMock);

        $this->pageByIdentifierMock->expects($this->never())
            ->method('execute');

        $this->widgetFilterMock->expects($this->never())
            ->method('filter');

        $this->expectException(NoSuchEntityException::class);

        $this->page->getDataByPageId(1);
    }

    /**
     * Test that page data is retrieved by identifier and all fields are returned when no ResolveInfo is provided
     *
     * @return void
     */
    public function testGetDataByPageIdentifierReturnsAllFieldsWhenInfoIsNull(): void
    {
        $pageMock = $this->getActivePageMock();

        $this->pageByIdentifierMock->expects($this->once())
            ->method('execute')
            ->with('page-identifier', 1)
            ->willReturn($pageMock);

        $this->pageRepositoryMock->expects($this->never())
            ->method('getById');

        $this->widgetFilterMock->expects($this->once())
            ->method('filter')
            ->with('page content')
            ->willReturn('filtered page content');

        $result = $this->page->getDataByPageIdentifier('page-identifier', 1);

        $this->assertSame($this->getExpectedPageData('filtered page content'), $result);
    }

    /**
     * Test that page data is retrieved by identifier and only the requested fields are returned
     *
     * @return void
     */
    public function testGetDataByPageIdentifierReturnsOnlyRequestedFields(): void
    {
        $pageMock = $this->getActivePageMock();

        $this->pageByIdentifierMock->expects($this->once())
            ->method('execute')
            ->with('page-identifier', 1)
            ->willReturn($pageMock);

        $this->pageRepositoryMock->expects($this->never())
            ->method('getById');

        $this->widgetFilterMock->expects($this->never())
            ->method('filter');

        $infoMock = $this->getResolveInfoMock(['meta_title' => 1]);

        $result = $this->page->getDataByPageIdentifier('page-identifier', 1, $infoMock);

        $expected = $this->getExpectedPageData();
        unset($expected[PageInterface::CONTENT]);

        $this->assertSame($expected, $result);
    }

    /**
     * Test that an exception is thrown when the page retrieved by identifier is inactive
     *
     * @return void
     */
    public function testGetDataByPageIdentifierThrowsExceptionWhenPageIsInactive(): void
    {
        $pageMock = $this->getInactivePageMock();

        $this->pageByIdentifierMock->expects($this->once())
            ->method('execute')
            ->with('page-identifier', 1)
            ->willReturn($pageMock);

        $this->pageRepositoryMock->expects($this->never())
            ->method('getById');

        $this->widgetFilterMock->expects($this->never())
            ->method('filter');

        $this->expectException(NoSuchEntityException::class);

        $this->page->getDataByPageIdentifier('page-identifier', 1);
    }

    /**
     * Build a stub of an active page with a fixed set of property values
     *
     * @return PageInterface|MockObject
     */
    private function getActivePageMock()
    {
        $pageMock = $this->createStub(PageInterface::class);
        $pageMock->method('isActive')->willReturn(true);
        $pageMock->method('getIdentifier')->willReturn('page-identifier');
        $pageMock->method('getTitle')->willReturn('Page Title');
        $pageMock->method('getContentHeading')->willReturn('Page Content Heading');
        $pageMock->method('getPageLayout')->willReturn('1column');
        $pageMock->method('getMetaTitle')->willReturn('Meta Title');
        $pageMock->method('getMetaDescription')->willReturn('Meta Description');
        $pageMock->method('getMetaKeywords')->willReturn('Meta Keywords');
        $pageMock->method('getId')->willReturn(1);
        $pageMock->method('getContent')->willReturn('page content');

        return $pageMock;
    }

    /**
     * Build a stub of an inactive page
     *
     * @return PageInterface|MockObject
     */
    private function getInactivePageMock()
    {
        $pageMock = $this->createStub(PageInterface::class);
        $pageMock->method('isActive')->willReturn(false);

        return $pageMock;
    }

    /**
     * Build a stub of ResolveInfo returning the given field selection
     *
     * @param array $fieldSelection
     * @return ResolveInfo|MockObject
     */
    private function getResolveInfoMock(array $fieldSelection)
    {
        $infoMock = $this->createStub(ResolveInfo::class);
        $infoMock->method('getFieldSelection')->willReturn($fieldSelection);

        return $infoMock;
    }

    /**
     * Expected page data array matching the values set on the active page mock
     *
     * @param string|null $content
     * @return array
     */
    private function getExpectedPageData(?string $content = 'page content'): array
    {
        $data = [
            'url_key' => 'page-identifier',
            PageInterface::TITLE => 'Page Title',
            PageInterface::CONTENT_HEADING => 'Page Content Heading',
            PageInterface::PAGE_LAYOUT => '1column',
            PageInterface::META_TITLE => 'Meta Title',
            PageInterface::META_DESCRIPTION => 'Meta Description',
            PageInterface::META_KEYWORDS => 'Meta Keywords',
            PageInterface::PAGE_ID => 1,
            PageInterface::IDENTIFIER => 'page-identifier',
        ];

        if ($content !== null) {
            $data[PageInterface::CONTENT] = $content;
        }

        return $data;
    }
}
