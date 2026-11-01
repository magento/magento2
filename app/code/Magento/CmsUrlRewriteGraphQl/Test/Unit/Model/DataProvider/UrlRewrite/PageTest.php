<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CmsUrlRewriteGraphQl\Test\Unit\Model\DataProvider\UrlRewrite;

use Magento\CmsGraphQl\Model\Resolver\DataProvider\Page as PageDataProvider;
use Magento\CmsUrlRewriteGraphQl\Model\DataProvider\UrlRewrite\Page;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see Page
 */
class PageTest extends TestCase
{
    /**
     * @var Page
     */
    private Page $page;

    /**
     * @var PageDataProvider|MockObject
     */
    private $pageDataProviderMock;

    /**
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void
    {
        $this->pageDataProviderMock = $this->createMock(PageDataProvider::class);
        $this->page = new Page($this->pageDataProviderMock);
    }

    /**
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testGetData(): void
    {
        $this->pageDataProviderMock
            ->expects($this->once())
            ->method('getDataByPageId')
            ->with(1, null)
            ->willReturn(['page_id' => 1, 'title' => 'Test Page']);

        $result = $this->page->getData('cms_page', 1);

        $this->assertEquals(
            ['page_id' => 1, 'title' => 'Test Page', 'type_id' => 'cms_page'],
            $result
        );
    }

    /**
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testGetDataWithResolveInfo(): void
    {
        $resolveInfoMock = $this->createMock(ResolveInfo::class);

        $this->pageDataProviderMock
            ->expects($this->once())
            ->method('getDataByPageId')
            ->with(2, $resolveInfoMock)
            ->willReturn(['page_id' => 2, 'title' => 'Another Page']);

        $result = $this->page->getData('cms_page', 2, $resolveInfoMock, 1);

        $this->assertEquals(
            ['page_id' => 2, 'title' => 'Another Page', 'type_id' => 'cms_page'],
            $result
        );
    }

    /**
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testGetDataThrowsNoSuchEntityException(): void
    {
        $this->pageDataProviderMock
            ->expects($this->once())
            ->method('getDataByPageId')
            ->with(999, null)
            ->willThrowException(new NoSuchEntityException());

        $this->expectException(NoSuchEntityException::class);

        $this->page->getData('cms_page', 999);
    }

    /**
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testGetDataThrowsLocalizedException(): void
    {
        $this->pageDataProviderMock
            ->expects($this->once())
            ->method('getDataByPageId')
            ->with(3, null)
            ->willThrowException(new LocalizedException(__('Something went wrong')));

        $this->expectException(LocalizedException::class);

        $this->page->getData('cms_page', 3);
    }
}
