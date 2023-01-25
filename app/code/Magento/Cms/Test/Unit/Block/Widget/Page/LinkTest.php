<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Block\Widget\Page;

use Magento\Cms\Block\Widget\Page\Link;
use Magento\Cms\Helper\Page;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LinkTest extends TestCase
{
    /**
     * @var Link
     */
    protected $linkElement;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Page|MockObject
     */
    protected $mockCmsPage;

    /**
     * @var \Magento\Cms\Model\ResourceModel\Page|MockObject
     */
    protected $mockResourcePage;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->mockCmsPage = $this->createMock(Page::class);
        $this->mockResourcePage = $this->createMock(\Magento\Cms\Model\ResourceModel\Page::class);

        $this->linkElement = $this->objectManager->getObject(
            Link::class,
            [
                'cmsPage' => $this->mockCmsPage,
                'resourcePage' => $this->mockResourcePage,
            ]
        );
    }

    protected function tearDown(): void
    {
        $this->linkElement = null;
    }

    public function testGetHrefEmpty()
    {
        $this->assertEmpty($this->linkElement->getHref());
    }

    public function testGetHref()
    {
        $href = 'localhost';
        $this->linkElement->setData('href', $href);
        $this->assertEquals($href, $this->linkElement->getHref());
    }

    public function testGetHrefByPageId()
    {
        $href = 'pagelink';
        $this->mockCmsPage->expects($this->once())
            ->method('getPageUrl')
            ->willReturn($href);
        $this->linkElement->setData('page_id', 1);
        $this->assertEquals($href, $this->linkElement->getHref());
    }

    public function testGetTitleEmpty()
    {
        $this->assertEmpty($this->linkElement->getTitle());
    }

    public function testGetTitle()
    {
        $title = 'Title';
        $this->linkElement->setData('title', $title);
        $this->assertEquals($title, $this->linkElement->getTitle());
    }

    public function testGetTitleByPageId()
    {
        $pageId = 1;
        $title = 'Title by page id';
        $this->mockResourcePage->expects($this->once())
            ->method('getCmsPageTitleById')
            ->with($pageId)
            ->willReturn($title);
        $this->linkElement->setData('page_id', $pageId);
        $this->assertEquals($title, $this->linkElement->getTitle());
    }

    public function testGetTitleByHref()
    {
        $href = 'localhost';
        $title = 'Title by href';
        $this->mockResourcePage->expects($this->once())
            ->method('setStore')
            ->willReturnSelf();
        $this->mockResourcePage->expects($this->once())
            ->method('getCmsPageTitleByIdentifier')
            ->with($href)
            ->willReturn($title);
        $this->linkElement->setData('href', $href);
        $this->assertEquals($title, $this->linkElement->getTitle());
    }

    public function testGetLabelByAnchorText()
    {
        $label = 'Test label';
        $this->linkElement->setData('anchor_text', $label);
        $this->assertEquals($label, $this->linkElement->getLabel());
    }

    public function testGetLabelLikeTitle()
    {
        $label = 'Test title';
        $this->linkElement->setData('title', $label);
        $this->assertEquals($label, $this->linkElement->getLabel());
    }

    public function testGetLabelByHref()
    {
        $href = 'localhost';
        $label = 'Label by href';
        $this->mockResourcePage->expects($this->once())
            ->method('setStore')
            ->willReturnSelf();
        $this->mockResourcePage->expects($this->once())
            ->method('getCmsPageTitleByIdentifier')
            ->with($href)
            ->willReturn($label);
        $this->linkElement->setData('href', $href);
        $this->assertEquals($label, $this->linkElement->getLabel());
    }

    public function testGetLabelByPageId()
    {
        $pageId = 1;
        $label = 'Label by page id';
        $this->mockResourcePage->expects($this->once())
            ->method('getCmsPageTitleById')
            ->with($pageId)
            ->willReturn($label);
        $this->linkElement->setData('page_id', $pageId);
        $this->assertEquals($label, $this->linkElement->getLabel());
    }

    public function testGetLabelEmpty()
    {
        $this->assertEmpty($this->linkElement->getLabel());
    }
}
