<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsUrlRewrite\Test\Unit\Model;

use Magento\Cms\Model\Page;
use Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator;
use Magento\CmsUrlRewrite\Model\CmsPageUrlRewriteGenerator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CmsPageUrlRewriteGeneratorTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var UrlRewriteFactory|MockObject
     */
    private $urlRewriteFactory;

    /**
     * @var CmsPageUrlPathGenerator|MockObject
     */
    private $urlPathGenerator;

    /**
     * @var CmsPageUrlRewriteGenerator
     */
    private $urlRewriteGenerator;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->urlRewriteFactory = $this->getMockBuilder(UrlRewriteFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlPathGenerator = $this->getMockBuilder(CmsPageUrlPathGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlRewriteGenerator = $this->objectManager->getObject(
            CmsPageUrlRewriteGenerator::class,
            [
                'storeManager' => $this->storeManager,
                'urlRewriteFactory' => $this->urlRewriteFactory,
                'cmsPageUrlPathGenerator' => $this->urlPathGenerator
            ]
        );
    }

    /**
     * @return void
     */
    public function testGenerateForAllStores(): void
    {
        $initializesStores = [0];
        $cmsPageId = 1;
        $cmsPage = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cmsPage->expects($this->any())->method('getStores')->willReturn($initializesStores);
        $store = $this->getMockBuilder(StoreInterface::class)
            ->addMethods(['getStoreId'])
            ->getMockForAbstractClass();
        $this->storeManager->expects($this->any())->method('getStores')->willReturn([$store]);
        $store->expects($this->any())->method('getStoreId')->willReturn($initializesStores[0]);
        $urlRewrite = $this->getMockBuilder(UrlRewrite::class)
            ->getMockForAbstractClass();
        $this->urlRewriteFactory->expects($this->any())->method('create')->willReturn($urlRewrite);
        $cmsPage->expects($this->any())->method('getId')->willReturn($cmsPageId);
        $cmsPage->expects($this->any())->method('getIdentifier')->willReturn('request_path');
        $this->urlPathGenerator->expects($this->any())->method('getCanonicalUrlPath')->with($cmsPage)
            ->willReturn('cms/page/view/page_id/' . $cmsPageId);

        $urls = $this->urlRewriteGenerator->generate($cmsPage);
        $this->assertEquals($initializesStores[0], $urls[0]->getStoreId());
        $this->assertArrayNotHasKey(1, $urls);
    }

    /**
     * @return void
     */
    public function testGenerateForSpecificStores(): void
    {
        $initializesStores = [1, 2];
        $cmsPageId = 1;
        $cmsPage = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cmsPage->expects($this->any())->method('getStores')->willReturn($initializesStores);
        $firstStore = $this->getMockBuilder(StoreInterface::class)
            ->addMethods(['getStoreId'])
            ->getMockForAbstractClass();
        $secondStore = $this->getMockBuilder(StoreInterface::class)
            ->addMethods(['getStoreId'])
            ->getMockForAbstractClass();
        $this->storeManager->expects($this->any())->method('getStores')->willReturn(
            [
                1 => $firstStore,
                2 => $secondStore
            ]
        );
        $firstStore->expects($this->any())->method('getStoreId')->willReturn($initializesStores[0]);
        $secondStore->expects($this->any())->method('getStoreId')->willReturn($initializesStores[1]);

        $urlRewriteFirst = $this->getMockBuilder(UrlRewrite::class)
            ->getMockForAbstractClass();
        $urlRewriteSecond = $this->getMockBuilder(UrlRewrite::class)
            ->getMockForAbstractClass();
        $this->urlRewriteFactory
            ->method('create')
            ->willReturnOnConsecutiveCalls($urlRewriteFirst, $urlRewriteSecond);

        $cmsPage->expects($this->any())->method('getId')->willReturn($cmsPageId);
        $cmsPage->expects($this->any())->method('getIdentifier')->willReturn('request_path');
        $this->urlPathGenerator->expects($this->any())->method('getCanonicalUrlPath')->with($cmsPage)
            ->willReturn('cms/page/view/page_id/' . $cmsPageId);
        $urls = $this->urlRewriteGenerator->generate($cmsPage);
        $this->assertEquals(
            [
                $initializesStores[0],
                $initializesStores[1]
            ],
            [
                $urls[0]->getStoreId(),
                $urls[1]->getStoreId()
            ]
        );
    }
}
