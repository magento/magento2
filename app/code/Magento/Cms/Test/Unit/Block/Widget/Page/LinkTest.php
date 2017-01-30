<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Block\Widget\Page;

class LinkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cms\Block\Widget\Page\Link
     */
    protected $linkElement;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Cms\Helper\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockCmsPage;

    /**
     * @var \Magento\Cms\Model\ResourceModel\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockResourcePage;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->mockCmsPage = $this->getMock('Magento\Cms\Helper\Page', [], [], '', false, false);
        $this->mockResourcePage = $this->getMock('Magento\Cms\Model\ResourceModel\Page', [], [], '', false, false);

        $this->linkElement = $this->objectManager->getObject(
            'Magento\Cms\Block\Widget\Page\Link',
            [
                'cmsPage' => $this->mockCmsPage,
                'resourcePage' => $this->mockResourcePage,
            ]
        );
    }

    protected function tearDown()
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
