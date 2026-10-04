<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CmsUrlRewrite\Test\Unit\Plugin\Cms\Model\ResourceModel;

use Magento\CmsUrlRewrite\Model\CmsPageUrlRewriteGenerator;
use Magento\CmsUrlRewrite\Plugin\Cms\Model\ResourceModel\Page;
use Magento\Cms\Model\Page as CmsPageModelPage;
use Magento\Cms\Model\ResourceModel\Page as CmsPageResourceModelPage;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PageTest extends TestCase
{
    /**
     * @var Page
     */
    protected $pageObject;

    /**
     * @var UrlPersistInterface|MockObject
     */
    protected $urlPersistMock;

    /**
     * @var CmsPageModelPage|MockObject
     */
    protected $cmsPageMock;

    /**
     * @var CmsPageResourceModelPage|MockObject
     */
    protected $cmsPageResourceMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->urlPersistMock = $this->createMock(UrlPersistInterface::class);

        $this->cmsPageMock = $this->getMockBuilder(CmsPageModelPage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cmsPageResourceMock = $this->getMockBuilder(CmsPageResourceModelPage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pageObject = $objectManager->getObject(
            Page::class,
            [
                'urlPersist' => $this->urlPersistMock
            ]
        );
    }

    public function testAfterDeletePositive()
    {
        $productId = 100;

        $this->cmsPageMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);

        $this->cmsPageMock->expects($this->once())
            ->method('isDeleted')
            ->willReturn(true);

        $this->urlPersistMock->expects($this->once())
            ->method('deleteByData')
            ->with(
                [
                    UrlRewrite::ENTITY_ID => $productId,
                    UrlRewrite::ENTITY_TYPE => CmsPageUrlRewriteGenerator::ENTITY_TYPE
                ]
            );

        $this->assertSame(
            $this->cmsPageResourceMock,
            $this->pageObject->afterDelete(
                $this->cmsPageResourceMock,
                $this->cmsPageResourceMock,
                $this->cmsPageMock
            )
        );
    }

    public function testAfterDeleteNegative()
    {
        $this->cmsPageMock->expects($this->once())
            ->method('isDeleted')
            ->willReturn(false);

        $this->urlPersistMock->expects($this->never())
            ->method('deleteByData');

        $this->assertSame(
            $this->cmsPageResourceMock,
            $this->pageObject->afterDelete(
                $this->cmsPageResourceMock,
                $this->cmsPageResourceMock,
                $this->cmsPageMock
            )
        );
    }
}
